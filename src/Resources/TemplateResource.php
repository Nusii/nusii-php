<?php

declare(strict_types=1);

namespace Nusii\Resources;

use Nusii\Data\PaginatedResponse;

class TemplateResource extends Resource
{
    public function list(int $page = 1, int $perPage = 25): PaginatedResponse
    {
        return $this->nusii->getCollection('templates', [
            'page' => $page,
            'per' => $perPage,
        ]);
    }

    public function get(int $id): array
    {
        return $this->nusii->getResource("templates/{$id}");
    }
}
