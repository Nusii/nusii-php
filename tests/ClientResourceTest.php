<?php

declare(strict_types=1);

use Nusii\Data\PaginatedResponse;
use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('ClientResource', function () {
    it('lists clients', function () {
        $mock = MockClient::create([
            TestResponse::collection('clients', [
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->clients()->list();

        expect($response)->toBeInstanceOf(PaginatedResponse::class);
        expect($response->data)->toHaveCount(2);
        expect($response->data[0]['name'])->toBe('John Doe');
        expect($response->data[1]['email'])->toBe('jane@example.com');
        expect($response->totalCount)->toBe(2);

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('GET');
        expect((string) $request->getUri())->toContain('/api/v2/clients');
    });

    it('lists clients with pagination', function () {
        $mock = MockClient::create([
            TestResponse::collection('clients', [
                ['id' => 3, 'name' => 'Bob', 'email' => 'bob@example.com'],
            ], ['current_page' => 2, 'next_page' => 3, 'prev_page' => 1, 'total_pages' => 5, 'total_count' => 50]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->clients()->list(page: 2, perPage: 10);

        expect($response->currentPage)->toBe(2);
        expect($response->nextPage)->toBe(3);
        expect($response->prevPage)->toBe(1);
        expect($response->totalPages)->toBe(5);
        expect($response->totalCount)->toBe(50);
        expect($response->hasNextPage())->toBeTrue();
        expect($response->hasPrevPage())->toBeTrue();

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['page'])->toBe('2');
        expect($query['per'])->toBe('10');
    });

    it('gets a single client', function () {
        $mock = MockClient::create([
            TestResponse::resource('clients', 42, [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'business' => 'Acme Inc',
                'telephone' => '555-1234',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $client = $nusii->clients()->get(42);

        expect($client['id'])->toBe(42);
        expect($client['name'])->toBe('John Doe');
        expect($client['business'])->toBe('Acme Inc');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('GET');
        expect((string) $request->getUri())->toContain('/api/v2/clients/42');
    });

    it('creates a client', function () {
        $mock = MockClient::create([
            TestResponse::resource('clients', 10, [
                'name' => 'New Client',
                'email' => 'new@example.com',
                'business' => 'New Corp',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $client = $nusii->clients()->create([
            'name' => 'New Client',
            'email' => 'new@example.com',
            'business' => 'New Corp',
        ]);

        expect($client['id'])->toBe(10);
        expect($client['name'])->toBe('New Client');
        expect($client['email'])->toBe('new@example.com');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('POST');
        expect((string) $request->getUri())->toContain('/api/v2/clients');

        $body = json_decode((string) $request->getBody(), true);
        expect($body)->toHaveKey('client');
        expect($body['client']['name'])->toBe('New Client');
        expect($body['client']['email'])->toBe('new@example.com');
    });

    it('updates a client', function () {
        $mock = MockClient::create([
            TestResponse::resource('clients', 42, [
                'name' => 'Updated Name',
                'email' => 'john@example.com',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $client = $nusii->clients()->update(42, ['name' => 'Updated Name']);

        expect($client['name'])->toBe('Updated Name');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('PUT');
        expect((string) $request->getUri())->toContain('/api/v2/clients/42');

        $body = json_decode((string) $request->getBody(), true);
        expect($body['client']['name'])->toBe('Updated Name');
    });

    it('deletes a client', function () {
        $mock = MockClient::create([
            TestResponse::noContent(),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->clients()->delete(42);

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('DELETE');
        expect((string) $request->getUri())->toContain('/api/v2/clients/42');
    });

    it('is iterable', function () {
        $mock = MockClient::create([
            TestResponse::collection('clients', [
                ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
                ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com'],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->clients()->list();

        $names = [];
        foreach ($response as $client) {
            $names[] = $client['name'];
        }

        expect($names)->toBe(['John', 'Jane']);
        expect(count($response))->toBe(2);
    });
});
