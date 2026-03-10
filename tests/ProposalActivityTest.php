<?php

declare(strict_types=1);

use Nusii\Data\PaginatedResponse;
use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('ProposalActivityResource', function () {
    it('lists activities', function () {
        $mock = MockClient::create([
            TestResponse::collection('proposal_activities', [
                [
                    'id' => 1,
                    'activity_type' => 'viewed',
                    'ip_address' => '192.168.1.1',
                    'proposal_title' => 'Website Redesign',
                    'client_name' => 'John',
                    'client_email' => 'john@example.com',
                ],
                [
                    'id' => 2,
                    'activity_type' => 'accepted',
                    'ip_address' => '192.168.1.2',
                    'proposal_title' => 'Mobile App',
                    'client_name' => 'Jane',
                    'client_email' => 'jane@example.com',
                ],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->proposalActivities()->list();

        expect($response)->toBeInstanceOf(PaginatedResponse::class);
        expect($response->data)->toHaveCount(2);
        expect($response->data[0]['activity_type'])->toBe('viewed');
        expect($response->data[1]['activity_type'])->toBe('accepted');
    });

    it('filters activities by proposal_id', function () {
        $mock = MockClient::create([
            TestResponse::collection('proposal_activities', []),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->proposalActivities()->list(proposalId: 42);

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['proposal_id'])->toBe('42');
    });

    it('filters activities by client_id', function () {
        $mock = MockClient::create([
            TestResponse::collection('proposal_activities', []),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->proposalActivities()->list(clientId: 10);

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['client_id'])->toBe('10');
    });

    it('gets a single activity', function () {
        $mock = MockClient::create([
            TestResponse::resource('proposal_activities', 5, [
                'activity_type' => 'viewed',
                'ip_address' => '10.0.0.1',
                'proposal_title' => 'Website Redesign',
                'proposal_status' => 'pending',
                'proposal_public_id' => 'abc123',
                'client_name' => 'John',
                'client_email' => 'john@example.com',
                'client_full_name' => 'John Doe',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $activity = $nusii->proposalActivities()->get(5);

        expect($activity['id'])->toBe(5);
        expect($activity['activity_type'])->toBe('viewed');
        expect($activity['proposal_title'])->toBe('Website Redesign');
        expect($activity['client_full_name'])->toBe('John Doe');

        $request = $mock->lastRequest();
        expect((string) $request->getUri())->toContain('/api/v2/proposal_activities/5');
    });
});
