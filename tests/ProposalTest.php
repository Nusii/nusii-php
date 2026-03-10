<?php

declare(strict_types=1);

use Nusii\Data\PaginatedResponse;
use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('ProposalResource', function () {
    it('lists proposals', function () {
        $mock = MockClient::create([
            TestResponse::collection('proposals', [
                ['id' => 1, 'title' => 'Website Redesign', 'status' => 'draft'],
                ['id' => 2, 'title' => 'Mobile App', 'status' => 'pending'],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->proposals()->list();

        expect($response)->toBeInstanceOf(PaginatedResponse::class);
        expect($response->data)->toHaveCount(2);
        expect($response->data[0]['title'])->toBe('Website Redesign');
        expect($response->data[1]['status'])->toBe('pending');
    });

    it('filters proposals by status', function () {
        $mock = MockClient::create([
            TestResponse::collection('proposals', [
                ['id' => 1, 'title' => 'Draft Proposal', 'status' => 'draft'],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->proposals()->list(status: 'draft');

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['status'])->toBe('draft');
    });

    it('filters proposals by archived', function () {
        $mock = MockClient::create([
            TestResponse::collection('proposals', []),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->proposals()->list(archived: true);

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['archived'])->toBe('1');
    });

    it('gets a single proposal', function () {
        $mock = MockClient::create([
            TestResponse::resource('proposals', 99, [
                'title' => 'Website Redesign',
                'status' => 'draft',
                'client_id' => 42,
                'client_email' => 'client@example.com',
                'public_id' => 'abc123',
                'currency' => 'EUR',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $proposal = $nusii->proposals()->get(99);

        expect($proposal['id'])->toBe(99);
        expect($proposal['title'])->toBe('Website Redesign');
        expect($proposal['status'])->toBe('draft');
        expect($proposal['client_id'])->toBe(42);
    });

    it('creates a proposal', function () {
        $mock = MockClient::create([
            TestResponse::resource('proposals', 100, [
                'title' => 'New Proposal',
                'status' => 'draft',
                'client_id' => 1,
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $proposal = $nusii->proposals()->create([
            'title' => 'New Proposal',
            'client_id' => 1,
        ]);

        expect($proposal['id'])->toBe(100);
        expect($proposal['title'])->toBe('New Proposal');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('POST');

        $body = json_decode((string) $request->getBody(), true);
        expect($body['proposal']['title'])->toBe('New Proposal');
        expect($body['proposal']['client_id'])->toBe(1);
    });

    it('updates a proposal', function () {
        $mock = MockClient::create([
            TestResponse::resource('proposals', 99, [
                'title' => 'Updated Title',
                'status' => 'draft',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $proposal = $nusii->proposals()->update(99, ['title' => 'Updated Title']);

        expect($proposal['title'])->toBe('Updated Title');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('PUT');
        expect((string) $request->getUri())->toContain('/api/v2/proposals/99');
    });

    it('deletes a proposal', function () {
        $mock = MockClient::create([
            TestResponse::noContent(),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->proposals()->delete(99);

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('DELETE');
        expect((string) $request->getUri())->toContain('/api/v2/proposals/99');
    });

    it('sends a proposal', function () {
        $mock = MockClient::create([
            TestResponse::json(200, [
                'status' => 'pending',
                'sent_at' => '2024-01-15T10:00:00Z',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $result = $nusii->proposals()->send(
            id: 99,
            email: 'client@example.com',
            subject: 'Your proposal',
            message: 'Please review the attached proposal.',
        );

        expect($result['status'])->toBe('pending');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('PUT');
        expect((string) $request->getUri())->toContain('/api/v2/proposals/99/send_proposal');

        $body = json_decode((string) $request->getBody(), true);
        expect($body['email'])->toBe('client@example.com');
        expect($body['subject'])->toBe('Your proposal');
        expect($body['message'])->toBe('Please review the attached proposal.');
    });

    it('sends a proposal with cc and bcc', function () {
        $mock = MockClient::create([
            TestResponse::json(200, ['status' => 'pending']),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->proposals()->send(
            id: 99,
            email: 'client@example.com',
            cc: 'manager@example.com',
            bcc: 'archive@example.com',
        );

        $request = $mock->lastRequest();
        $body = json_decode((string) $request->getBody(), true);
        expect($body['cc'])->toBe('manager@example.com');
        expect($body['bcc'])->toBe('archive@example.com');
    });

    it('archives a proposal', function () {
        $mock = MockClient::create([
            TestResponse::resource('proposals', 99, [
                'title' => 'Archived Proposal',
                'status' => 'accepted',
                'archived_at' => '2024-01-20T10:00:00Z',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $proposal = $nusii->proposals()->archive(99);

        expect($proposal['id'])->toBe(99);
        expect($proposal['archived_at'])->toBe('2024-01-20T10:00:00Z');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('PUT');
        expect((string) $request->getUri())->toContain('/api/v2/proposals/99/archive');
    });
});
