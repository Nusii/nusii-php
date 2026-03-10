<?php

declare(strict_types=1);

namespace Nusii\Resources;

use Nusii\Data\PaginatedResponse;

class SectionResource extends Resource
{
    public function list(
        int $page = 1,
        int $perPage = 25,
        ?int $proposalId = null,
        ?int $templateId = null,
        ?bool $includeLineItems = null,
    ): PaginatedResponse {
        return $this->nusii->getCollection('sections', array_filter([
            'page' => $page,
            'per' => $perPage,
            'proposal_id' => $proposalId,
            'template_id' => $templateId,
            'include_line_items' => $includeLineItems,
        ], fn ($v) => $v !== null));
    }

    public function get(int $id): array
    {
        return $this->nusii->getResource("sections/{$id}");
    }

    public function create(array $attributes): array
    {
        return $this->nusii->createResource('sections', 'section', $attributes);
    }

    public function update(int $id, array $attributes): array
    {
        return $this->nusii->updateResource("sections/{$id}", 'section', $attributes);
    }

    public function delete(int $id): void
    {
        $this->nusii->deleteResource("sections/{$id}");
    }
}
