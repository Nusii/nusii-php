<?php

declare(strict_types=1);

namespace Tests\TestDoubles;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class MockClient
{
    private array $history = [];
    private Client $guzzleClient;

    private function __construct() {}

    public static function create(array $responses): self
    {
        $instance = new self();
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($instance->history));
        $instance->guzzleClient = new Client(['handler' => $handlerStack]);

        return $instance;
    }

    public function client(): Client
    {
        return $this->guzzleClient;
    }

    public function lastRequest(): RequestInterface
    {
        return end($this->history)['request'];
    }

    /**
     * @return array<int, array{request: RequestInterface, response: \Psr\Http\Message\ResponseInterface}>
     */
    public function history(): array
    {
        return $this->history;
    }

    public function requestCount(): int
    {
        return count($this->history);
    }
}
