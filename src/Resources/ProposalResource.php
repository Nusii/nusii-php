<?php

declare(strict_types=1);

namespace Nusii\Resources;

use GuzzleHttp\RequestOptions;
use Nusii\Data\PaginatedResponse;

class ProposalResource extends Resource
{
    public function list(
        int $page = 1,
        int $perPage = 25,
        ?string $status = null,
        ?bool $archived = null,
    ): PaginatedResponse {
        return $this->nusii->getCollection('proposals', array_filter([
            'page' => $page,
            'per' => $perPage,
            'status' => $status,
            'archived' => $archived,
        ], fn ($v) => $v !== null));
    }

    public function get(int $id): array
    {
        return $this->nusii->getResource("proposals/{$id}");
    }

    public function create(array $attributes): array
    {
        return $this->nusii->createResource('proposals', 'proposal', $attributes);
    }

    public function update(int $id, array $attributes): array
    {
        return $this->nusii->updateResource("proposals/{$id}", 'proposal', $attributes);
    }

    public function delete(int $id): void
    {
        $this->nusii->deleteResource("proposals/{$id}");
    }

    public function send(
        int $id,
        string $email,
        ?string $cc = null,
        ?string $bcc = null,
        ?string $subject = null,
        ?string $message = null,
    ): array {
        return $this->nusii->sendRaw('PUT', "proposals/{$id}/send_proposal", [
            RequestOptions::JSON => array_filter([
                'email' => $email,
                'cc' => $cc,
                'bcc' => $bcc,
                'subject' => $subject,
                'message' => $message,
            ], fn ($v) => $v !== null),
        ]);
    }

    public function archive(int $id): array
    {
        $response = $this->nusii->sendRaw('PUT', "proposals/{$id}/archive");

        return $this->nusii->parseResource($response);
    }
}
