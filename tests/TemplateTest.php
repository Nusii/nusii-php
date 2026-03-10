<?php

declare(strict_types=1);

use Nusii\Data\PaginatedResponse;
use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('TemplateResource', function () {
    it('lists templates', function () {
        $mock = MockClient::create([
            TestResponse::collection('templates', [
                ['id' => 1, 'name' => 'Default Template', 'public_template' => false, 'dummy_template' => false],
                ['id' => 2, 'name' => 'Web Project', 'public_template' => true, 'dummy_template' => false],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->templates()->list();

        expect($response)->toBeInstanceOf(PaginatedResponse::class);
        expect($response->data)->toHaveCount(2);
        expect($response->data[0]['name'])->toBe('Default Template');
        expect($response->data[1]['name'])->toBe('Web Project');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('GET');
        expect((string) $request->getUri())->toContain('/api/v2/templates');
    });

    it('gets a single template', function () {
        $mock = MockClient::create([
            TestResponse::resource('templates', 5, [
                'name' => 'Web Project Template',
                'account_id' => 1,
                'public_template' => false,
                'dummy_template' => false,
                'created_at' => '2024-01-01T00:00:00Z',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $template = $nusii->templates()->get(5);

        expect($template['id'])->toBe(5);
        expect($template['name'])->toBe('Web Project Template');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('GET');
        expect((string) $request->getUri())->toContain('/api/v2/templates/5');
    });

    it('paginates templates', function () {
        $mock = MockClient::create([
            TestResponse::collection('templates', [
                ['id' => 1, 'name' => 'Template 1'],
            ], ['current_page' => 1, 'total_pages' => 2, 'total_count' => 15]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->templates()->list(page: 1, perPage: 10);

        expect($response->totalCount)->toBe(15);
        expect($response->hasNextPage())->toBeFalse(); // next_page is null in default meta

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['per'])->toBe('10');
    });
});
