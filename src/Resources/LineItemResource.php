<?php

declare(strict_types=1);

namespace Nusii\Resources;

use Nusii\Data\PaginatedResponse;

class LineItemResource extends Resource
{
    public function list(int $page = 1, int $perPage = 25): PaginatedResponse
    {
        return $this->nusii->getCollection('line_items', [
            'page' => $page,
            'per' => $perPage,
        ]);
    }

    public function listBySection(int $sectionId, int $page = 1, int $perPage = 25): PaginatedResponse
    {
        return $this->nusii->getCollection("sections/{$sectionId}/line_items", [
            'page' => $page,
            'per' => $perPage,
        ]);
    }

    public function get(int $id): array
    {
        return $this->nusii->getResource("line_items/{$id}");
    }

    public function createForSection(int $sectionId, array $attributes): array
    {
        return $this->nusii->createResource("sections/{$sectionId}/line_items", 'line_item', $attributes);
    }

    public function update(int $id, array $attributes): array
    {
        return $this->nusii->updateResource("line_items/{$id}", 'line_item', $attributes);
    }

    public function delete(int $id): void
    {
        $this->nusii->deleteResource("line_items/{$id}");
    }
}
