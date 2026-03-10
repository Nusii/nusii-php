<?php

declare(strict_types=1);

namespace Nusii\Resources;

use Nusii\Data\PaginatedResponse;

class ClientResource extends Resource
{
    public function list(int $page = 1, int $perPage = 25): PaginatedResponse
    {
        return $this->nusii->getCollection('clients', [
            'page' => $page,
            'per' => $perPage,
        ]);
    }

    public function get(int $id): array
    {
        return $this->nusii->getResource("clients/{$id}");
    }

    public function create(array $attributes): array
    {
        return $this->nusii->createResource('clients', 'client', $attributes);
    }

    public function update(int $id, array $attributes): array
    {
        return $this->nusii->updateResource("clients/{$id}", 'client', $attributes);
    }

    public function delete(int $id): void
    {
        $this->nusii->deleteResource("clients/{$id}");
    }
}
