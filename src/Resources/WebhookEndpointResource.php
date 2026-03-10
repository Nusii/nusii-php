<?php

declare(strict_types=1);

namespace Nusii\Resources;

use Nusii\Data\PaginatedResponse;

class WebhookEndpointResource extends Resource
{
    public function list(int $page = 1, int $perPage = 25): PaginatedResponse
    {
        return $this->nusii->getCollection('webhook_endpoints', [
            'page' => $page,
            'per' => $perPage,
        ]);
    }

    public function get(int $id): array
    {
        return $this->nusii->getResource("webhook_endpoints/{$id}");
    }

    public function create(array $attributes): array
    {
        return $this->nusii->createResource('webhook_endpoints', 'webhook_endpoint', $attributes);
    }

    public function delete(int $id): void
    {
        $this->nusii->deleteResource("webhook_endpoints/{$id}");
    }
}
