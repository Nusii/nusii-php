<?php

declare(strict_types=1);

use Nusii\Data\PaginatedResponse;
use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('LineItemResource', function () {
    it('lists all line items', function () {
        $mock = MockClient::create([
            TestResponse::collection('line_items', [
                ['id' => 1, 'name' => 'Design', 'quantity' => 10, 'amount_in_cents' => 5000],
                ['id' => 2, 'name' => 'Development', 'quantity' => 20, 'amount_in_cents' => 7500],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->lineItems()->list();

        expect($response)->toBeInstanceOf(PaginatedResponse::class);
        expect($response->data)->toHaveCount(2);
        expect($response->data[0]['name'])->toBe('Design');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('GET');
        expect((string) $request->getUri())->toContain('/api/v2/line_items');
    });

    it('lists line items by section', function () {
        $mock = MockClient::create([
            TestResponse::collection('line_items', [
                ['id' => 1, 'name' => 'Design', 'section_id' => 5],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->lineItems()->listBySection(5);

        expect($response->data)->toHaveCount(1);

        $request = $mock->lastRequest();
        expect((string) $request->getUri())->toContain('/api/v2/sections/5/line_items');
    });

    it('gets a single line item', function () {
        $mock = MockClient::create([
            TestResponse::resource('line_items', 3, [
                'name' => 'Design Work',
                'section_id' => 5,
                'quantity' => 10,
                'cost_type' => 'fixed',
                'recurring_type' => null,
                'per_type' => 'hour',
                'currency' => 'USD',
                'amount_in_cents' => 5000,
                'amount_formatted' => '$50.00',
                'total_in_cents' => 50000,
                'total_formatted' => '$500.00',
                'position' => 1,
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $item = $nusii->lineItems()->get(3);

        expect($item['id'])->toBe(3);
        expect($item['name'])->toBe('Design Work');
        expect($item['quantity'])->toBe(10);
        expect($item['total_formatted'])->toBe('$500.00');

        $request = $mock->lastRequest();
        expect((string) $request->getUri())->toContain('/api/v2/line_items/3');
    });

    it('creates a line item for a section', function () {
        $mock = MockClient::create([
            TestResponse::resource('line_items', 10, [
                'name' => 'UX Design',
                'quantity' => 5,
                'amount' => 100,
                'cost_type' => 'hourly',
                'section_id' => 7,
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $item = $nusii->lineItems()->createForSection(7, [
            'name' => 'UX Design',
            'quantity' => 5,
            'amount' => 100,
            'cost_type' => 'hourly',
        ]);

        expect($item['id'])->toBe(10);
        expect($item['name'])->toBe('UX Design');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('POST');
        expect((string) $request->getUri())->toContain('/api/v2/sections/7/line_items');

        $body = json_decode((string) $request->getBody(), true);
        expect($body['line_item']['name'])->toBe('UX Design');
        expect($body['line_item']['quantity'])->toBe(5);
    });

    it('updates a line item', function () {
        $mock = MockClient::create([
            TestResponse::resource('line_items', 3, [
                'name' => 'Updated Item',
                'quantity' => 15,
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $item = $nusii->lineItems()->update(3, [
            'name' => 'Updated Item',
            'quantity' => 15,
        ]);

        expect($item['name'])->toBe('Updated Item');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('PUT');
        expect((string) $request->getUri())->toContain('/api/v2/line_items/3');

        $body = json_decode((string) $request->getBody(), true);
        expect($body['line_item']['name'])->toBe('Updated Item');
    });

    it('deletes a line item', function () {
        $mock = MockClient::create([
            TestResponse::noContent(),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->lineItems()->delete(3);

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('DELETE');
        expect((string) $request->getUri())->toContain('/api/v2/line_items/3');
    });

    it('paginates line items by section', function () {
        $mock = MockClient::create([
            TestResponse::collection('line_items', [
                ['id' => 1, 'name' => 'Item 1'],
            ], ['current_page' => 2, 'total_pages' => 3, 'total_count' => 25]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->lineItems()->listBySection(5, page: 2, perPage: 10);

        expect($response->currentPage)->toBe(2);
        expect($response->totalPages)->toBe(3);

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['page'])->toBe('2');
        expect($query['per'])->toBe('10');
    });
});
