<?php

declare(strict_types=1);

use Nusii\Data\PaginatedResponse;
use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('WebhookEndpointResource', function () {
    it('lists webhook endpoints', function () {
        $mock = MockClient::create([
            TestResponse::collection('webhook_endpoints', [
                ['id' => 1, 'target_url' => 'https://example.com/webhook1', 'events' => ['proposal.accepted']],
                ['id' => 2, 'target_url' => 'https://example.com/webhook2', 'events' => ['proposal.sent', 'proposal.viewed']],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->webhookEndpoints()->list();

        expect($response)->toBeInstanceOf(PaginatedResponse::class);
        expect($response->data)->toHaveCount(2);
        expect($response->data[0]['target_url'])->toBe('https://example.com/webhook1');
        expect($response->data[1]['events'])->toBe(['proposal.sent', 'proposal.viewed']);
    });

    it('gets a single webhook endpoint', function () {
        $mock = MockClient::create([
            TestResponse::resource('webhook_endpoints', 1, [
                'target_url' => 'https://example.com/webhook',
                'events' => ['proposal.accepted', 'proposal.rejected'],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $webhook = $nusii->webhookEndpoints()->get(1);

        expect($webhook['id'])->toBe(1);
        expect($webhook['target_url'])->toBe('https://example.com/webhook');
        expect($webhook['events'])->toContain('proposal.accepted');

        $request = $mock->lastRequest();
        expect((string) $request->getUri())->toContain('/api/v2/webhook_endpoints/1');
    });

    it('creates a webhook endpoint', function () {
        $mock = MockClient::create([
            TestResponse::resource('webhook_endpoints', 5, [
                'target_url' => 'https://example.com/new-webhook',
                'events' => ['proposal.sent'],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $webhook = $nusii->webhookEndpoints()->create([
            'target_url' => 'https://example.com/new-webhook',
            'events' => ['proposal.sent'],
        ]);

        expect($webhook['id'])->toBe(5);
        expect($webhook['target_url'])->toBe('https://example.com/new-webhook');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('POST');

        $body = json_decode((string) $request->getBody(), true);
        expect($body['webhook_endpoint']['target_url'])->toBe('https://example.com/new-webhook');
        expect($body['webhook_endpoint']['events'])->toBe(['proposal.sent']);
    });

    it('deletes a webhook endpoint', function () {
        $mock = MockClient::create([
            TestResponse::noContent(),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->webhookEndpoints()->delete(1);

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('DELETE');
        expect((string) $request->getUri())->toContain('/api/v2/webhook_endpoints/1');
    });
});
