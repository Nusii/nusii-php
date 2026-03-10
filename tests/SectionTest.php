<?php

declare(strict_types=1);

use Nusii\Data\PaginatedResponse;
use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('SectionResource', function () {
    it('lists sections', function () {
        $mock = MockClient::create([
            TestResponse::collection('sections', [
                ['id' => 1, 'title' => 'Introduction', 'body' => '<p>Hello</p>', 'position' => 1],
                ['id' => 2, 'title' => 'Pricing', 'body' => '<p>Costs</p>', 'position' => 2],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $response = $nusii->sections()->list();

        expect($response)->toBeInstanceOf(PaginatedResponse::class);
        expect($response->data)->toHaveCount(2);
        expect($response->data[0]['title'])->toBe('Introduction');
    });

    it('filters sections by proposal_id', function () {
        $mock = MockClient::create([
            TestResponse::collection('sections', [
                ['id' => 1, 'title' => 'Section 1', 'proposal_id' => 5],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->sections()->list(proposalId: 5);

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['proposal_id'])->toBe('5');
    });

    it('filters sections by template_id', function () {
        $mock = MockClient::create([
            TestResponse::collection('sections', []),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->sections()->list(templateId: 3);

        $request = $mock->lastRequest();
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        expect($query['template_id'])->toBe('3');
    });

    it('gets a single section', function () {
        $mock = MockClient::create([
            TestResponse::resource('sections', 7, [
                'title' => 'Project Scope',
                'body' => '<p>Detailed scope</p>',
                'position' => 1,
                'section_type' => 'cost',
                'proposal_id' => 99,
                'optional' => false,
                'include_total' => true,
                'total_in_cents' => 50000,
                'total_formatted' => '$500.00',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $section = $nusii->sections()->get(7);

        expect($section['id'])->toBe(7);
        expect($section['title'])->toBe('Project Scope');
        expect($section['section_type'])->toBe('cost');
        expect($section['total_formatted'])->toBe('$500.00');
    });

    it('creates a section', function () {
        $mock = MockClient::create([
            TestResponse::resource('sections', 15, [
                'title' => 'New Section',
                'body' => '<p>Content</p>',
                'proposal_id' => 99,
                'position' => 3,
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $section = $nusii->sections()->create([
            'title' => 'New Section',
            'body' => '<p>Content</p>',
            'proposal_id' => 99,
            'position' => 3,
        ]);

        expect($section['id'])->toBe(15);
        expect($section['title'])->toBe('New Section');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('POST');

        $body = json_decode((string) $request->getBody(), true);
        expect($body['section']['title'])->toBe('New Section');
        expect($body['section']['proposal_id'])->toBe(99);
    });

    it('updates a section', function () {
        $mock = MockClient::create([
            TestResponse::resource('sections', 7, [
                'title' => 'Updated Title',
                'body' => '<p>Updated</p>',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $section = $nusii->sections()->update(7, [
            'title' => 'Updated Title',
            'body' => '<p>Updated</p>',
        ]);

        expect($section['title'])->toBe('Updated Title');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('PUT');
        expect((string) $request->getUri())->toContain('/api/v2/sections/7');
    });

    it('deletes a section', function () {
        $mock = MockClient::create([
            TestResponse::noContent(),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->sections()->delete(7);

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('DELETE');
        expect((string) $request->getUri())->toContain('/api/v2/sections/7');
    });
});
