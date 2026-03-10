<?php

declare(strict_types=1);

use Nusii\Data\PaginatedResponse;
use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('UserResource', function () {
    it('lists users', function () {
        $mock = MockClient::create([
            TestResponse::collection('users', [
                ['id' => 1, 'email' => 'admin@example.com', 'name' => 'Admin User'],
                ['id' => 2, 'email' => 'team@example.com', 'name' => 'Team Member'],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->users()->list();

        expect($response)->toBeInstanceOf(PaginatedResponse::class);
        expect($response->data)->toHaveCount(2);
        expect($response->data[0]['email'])->toBe('admin@example.com');
        expect($response->data[1]['name'])->toBe('Team Member');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('GET');
        expect((string) $request->getUri())->toContain('/api/v2/users');
    });

    it('paginates users', function () {
        $mock = MockClient::create([
            TestResponse::collection('users', [
                ['id' => 1, 'email' => 'user@example.com', 'name' => 'User'],
            ], ['current_page' => 2, 'total_pages' => 3, 'total_count' => 30]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->users()->list(page: 2, perPage: 10);

        expect($response->currentPage)->toBe(2);
        expect($response->totalCount)->toBe(30);

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['page'])->toBe('2');
        expect($query['per'])->toBe('10');
    });
});
