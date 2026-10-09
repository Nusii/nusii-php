<?php

declare(strict_types=1);

namespace Nusii\Resources;

use Nusii\Data\PaginatedResponse;

class TemplateResource extends Resource
{
    /**
     * The account's own templates, or Nusii's public templates with $publicTemplates.
     */
    public function list(int $page = 1, int $perPage = 25, bool $publicTemplates = false): PaginatedResponse
    {
        return $this->nusii->getCollection('templates', [
            'page' => $page,
            'per' => $perPage,
            // The API only switches to public templates on the literal string "true".
            'public_templates' => $publicTemplates ? 'true' : null,
        ]);
    }

    public function get(int $id): array
    {
        return $this->nusii->getResource("templates/{$id}");
    }
}
