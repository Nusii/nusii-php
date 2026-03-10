<?php

declare(strict_types=1);

namespace Nusii\Resources;

use Nusii\Data\PaginatedResponse;

class ProposalActivityResource extends Resource
{
    public function list(
        int $page = 1,
        int $perPage = 25,
        ?int $proposalId = null,
        ?int $clientId = null,
    ): PaginatedResponse {
        return $this->nusii->getCollection('proposal_activities', array_filter([
            'page' => $page,
            'per' => $perPage,
            'proposal_id' => $proposalId,
            'client_id' => $clientId,
        ], fn ($v) => $v !== null));
    }

    public function get(int $id): array
    {
        return $this->nusii->getResource("proposal_activities/{$id}");
    }
}
