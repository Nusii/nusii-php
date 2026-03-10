<?php

declare(strict_types=1);

namespace Nusii\Concerns;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Nusii\Data\PaginatedResponse;
use Nusii\Exceptions\AuthenticationException;
use Nusii\Exceptions\NotFoundException;
use Nusii\Exceptions\NusiiException;
use Nusii\Exceptions\RateLimitException;
use Nusii\Exceptions\ServerException;
use Nusii\Exceptions\ValidationException;
use Nusii\Nusii;
use Psr\Http\Message\ResponseInterface;

trait SendsRequests
{
    public function getResource(string $uri): array
    {
        $response = $this->sendRequest('GET', $uri);

        return $this->parseResource($response);
    }

    public function getCollection(string $uri, array $query = []): PaginatedResponse
    {
        $response = $this->sendRequest('GET', $uri, [
            RequestOptions::QUERY => array_filter($query, fn ($v) => $v !== null),
        ]);

        return $this->parseCollection($response);
    }

    public function createResource(string $uri, string $resourceKey, array $data): array
    {
        $response = $this->sendRequest('POST', $uri, [
            RequestOptions::JSON => [$resourceKey => $data],
        ]);

        return $this->parseResource($response);
    }

    public function updateResource(string $uri, string $resourceKey, array $data): array
    {
        $response = $this->sendRequest('PUT', $uri, [
            RequestOptions::JSON => [$resourceKey => $data],
        ]);

        return $this->parseResource($response);
    }

    public function deleteResource(string $uri): void
    {
        $this->sendRequest('DELETE', $uri);
    }

    public function sendRaw(string $method, string $uri, array $options = []): array
    {
        return $this->sendRequest($method, $uri, $options);
    }

    public function parseResource(array $response): array
    {
        if (! isset($response['data'])) {
            return $response;
        }

        $data = $response['data'];

        return [
            'id' => (int) $data['id'],
            ...$data['attributes'],
        ];
    }

    public function parseCollection(array $response): PaginatedResponse
    {
        $items = array_map(fn (array $item) => [
            'id' => (int) $item['id'],
            ...$item['attributes'],
        ], $response['data'] ?? []);

        $meta = $response['meta'] ?? [];

        return new PaginatedResponse(
            data: $items,
            currentPage: $meta['current_page'] ?? 1,
            nextPage: $meta['next_page'] ?? null,
            prevPage: $meta['prev_page'] ?? null,
            totalPages: $meta['total_pages'] ?? 1,
            totalCount: $meta['total_count'] ?? count($items),
        );
    }

    protected function sendRequest(string $method, string $uri, array $options = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/api/v2/' . ltrim($uri, '/');

        $options[RequestOptions::HEADERS] = array_merge(
            $options[RequestOptions::HEADERS] ?? [],
            $this->defaultHeaders(),
        );

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $this->updateRateLimits($response);

            $body = (string) $response->getBody();

            return $body !== '' ? json_decode($body, true, 512, JSON_THROW_ON_ERROR) : [];
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => "Token token={$this->apiKey}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'nusii-php/' . Nusii::VERSION,
        ];
    }

    protected function handleException(RequestException $e): never
    {
        $response = $e->getResponse();
        $statusCode = $response?->getStatusCode() ?? 0;
        $body = null;
        $message = $e->getMessage();

        if ($response) {
            $rawBody = (string) $response->getBody();
            if ($rawBody !== '') {
                $body = json_decode($rawBody, true);
                $message = $body['error'] ?? $message;
            }
        }

        throw match (true) {
            $statusCode === 401 => new AuthenticationException($message, $statusCode, $body, $e),
            $statusCode === 404 => new NotFoundException($message, $statusCode, $body, $e),
            $statusCode === 429 => new RateLimitException(
                $message,
                $statusCode,
                retryAfter: $response ? ((int) $response->getHeaderLine('x-ratelimit-retry-after') ?: null) : null,
                responseBody: $body,
                previous: $e,
            ),
            $statusCode >= 400 && $statusCode < 500 => new ValidationException($message, $statusCode, $body, $e),
            $statusCode >= 500 => new ServerException($message, $statusCode, $body, $e),
            default => new NusiiException($message, $statusCode, $body, $e),
        };
    }

    protected function updateRateLimits(ResponseInterface $response): void
    {
        $remaining = $response->getHeaderLine('x-ratelimit-remaining');
        if ($remaining !== '') {
            $this->rateLimitRemaining = (int) $remaining;
        }

        $retryAfter = $response->getHeaderLine('x-ratelimit-retry-after');
        if ($retryAfter !== '') {
            $this->rateLimitRetryAfter = (int) $retryAfter;
        }
    }
}
