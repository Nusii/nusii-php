<?php

declare(strict_types=1);

namespace Nusii\Resources;

use Nusii\Data\PaginatedResponse;

class UserResource extends Resource
{
    public function list(int $page = 1, int $perPage = 25): PaginatedResponse
    {
        return $this->nusii->getCollection('users', [
            'page' => $page,
            'per' => $perPage,
        ]);
    }
}
