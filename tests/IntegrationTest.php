<?php

declare(strict_types=1);

use Nusii\Data\PaginatedResponse;
use Nusii\Exceptions\NotFoundException;
use Nusii\Nusii;

/**
 * Integration tests that run against a live Nusii API server.
 * Set NUSII_API_KEY and NUSII_API_URL environment variables to run these.
 */

beforeEach(function () {
    $apiKey = getenv('NUSII_API_KEY');
    $apiUrl = getenv('NUSII_API_URL') ?: 'http://localhost:3000';

    if (! $apiKey) {
        $this->markTestSkipped('NUSII_API_KEY not set');
    }

    $this->nusii = new Nusii($apiKey, baseUrl: $apiUrl);
});

describe('Integration: Account', function () {
    it('fetches the current account', function () {
        $account = $this->nusii->accounts()->me();

        expect($account)->toHaveKey('id');
        expect($account)->toHaveKey('email');
        expect($account)->toHaveKey('name');
        expect($account)->toHaveKey('currency');
    });
});

describe('Integration: Clients', function () {
    it('performs full CRUD lifecycle', function () {
        // Create
        $client = $this->nusii->clients()->create([
            'name' => 'Integration',
            'surname' => 'Test',
            'email' => 'integration-' . time() . '@test.com',
            'business' => 'Test Corp',
        ]);

        expect($client)->toHaveKey('id');
        expect($client['name'])->toBe('Integration');
        expect($client['surname'])->toBe('Test');
        $clientId = $client['id'];

        // Read
        $fetched = $this->nusii->clients()->get($clientId);
        expect($fetched['id'])->toBe($clientId);
        expect($fetched['name'])->toBe('Integration');

        // Update
        $updated = $this->nusii->clients()->update($clientId, [
            'name' => 'Updated Integration',
        ]);
        expect($updated['name'])->toBe('Updated Integration');

        // List
        $list = $this->nusii->clients()->list();
        expect($list)->toBeInstanceOf(PaginatedResponse::class);
        expect($list->totalCount)->toBeGreaterThanOrEqual(1);

        // Delete
        $this->nusii->clients()->delete($clientId);

        // Verify deleted
        expect(fn () => $this->nusii->clients()->get($clientId))
            ->toThrow(NotFoundException::class);
    });
});

describe('Integration: Proposals', function () {
    it('performs full CRUD lifecycle', function () {
        // Create a client first
        $client = $this->nusii->clients()->create([
            'name' => 'Proposal',
            'surname' => 'TestClient',
            'email' => 'proposal-test-' . time() . '@test.com',
        ]);

        // Create proposal
        $proposal = $this->nusii->proposals()->create([
            'title' => 'Integration Test Proposal',
            'client_id' => $client['id'],
        ]);

        expect($proposal)->toHaveKey('id');
        expect($proposal['title'])->toBe('Integration Test Proposal');
        $proposalId = $proposal['id'];

        // Read
        $fetched = $this->nusii->proposals()->get($proposalId);
        expect($fetched['id'])->toBe($proposalId);
        expect($fetched['title'])->toBe('Integration Test Proposal');

        // Update
        $updated = $this->nusii->proposals()->update($proposalId, [
            'title' => 'Updated Proposal Title',
        ]);
        expect($updated['title'])->toBe('Updated Proposal Title');

        // List
        $list = $this->nusii->proposals()->list();
        expect($list)->toBeInstanceOf(PaginatedResponse::class);
        expect($list->totalCount)->toBeGreaterThanOrEqual(1);

        // List with status filter
        $drafts = $this->nusii->proposals()->list(status: 'draft');
        expect($drafts)->toBeInstanceOf(PaginatedResponse::class);

        // Delete proposal
        $this->nusii->proposals()->delete($proposalId);

        // Cleanup client
        $this->nusii->clients()->delete($client['id']);
    });
});

describe('Integration: Sections', function () {
    it('performs full CRUD lifecycle', function () {
        // Create client and proposal first
        $client = $this->nusii->clients()->create([
            'name' => 'Section',
            'surname' => 'TestClient',
            'email' => 'section-test-' . time() . '@test.com',
        ]);

        $proposal = $this->nusii->proposals()->create([
            'title' => 'Section Test Proposal',
            'client_id' => $client['id'],
        ]);

        // Create section
        $section = $this->nusii->sections()->create([
            'proposal_id' => $proposal['id'],
            'title' => 'Test Section',
            'body' => '<p>This is a test section body.</p>',
        ]);

        expect($section)->toHaveKey('id');
        expect($section['title'])->toBe('Test Section');
        $sectionId = $section['id'];

        // Read
        $fetched = $this->nusii->sections()->get($sectionId);
        expect($fetched['id'])->toBe($sectionId);

        // Update
        $updated = $this->nusii->sections()->update($sectionId, [
            'title' => 'Updated Section Title',
        ]);
        expect($updated['title'])->toBe('Updated Section Title');

        // List by proposal
        $sections = $this->nusii->sections()->list(proposalId: $proposal['id']);
        expect($sections)->toBeInstanceOf(PaginatedResponse::class);
        expect($sections->totalCount)->toBeGreaterThanOrEqual(1);

        // Delete section
        $this->nusii->sections()->delete($sectionId);

        // Cleanup
        $this->nusii->proposals()->delete($proposal['id']);
        $this->nusii->clients()->delete($client['id']);
    });
});

describe('Integration: Line Items', function () {
    it('performs full CRUD lifecycle', function () {
        // Create client, proposal, and section first
        $client = $this->nusii->clients()->create([
            'name' => 'LineItem',
            'surname' => 'TestClient',
            'email' => 'lineitem-test-' . time() . '@test.com',
        ]);

        $proposal = $this->nusii->proposals()->create([
            'title' => 'LineItem Test Proposal',
            'client_id' => $client['id'],
        ]);

        $section = $this->nusii->sections()->create([
            'proposal_id' => $proposal['id'],
            'title' => 'Cost Section',
            'section_type' => 'cost',
        ]);

        // Create line item
        $lineItem = $this->nusii->lineItems()->createForSection($section['id'], [
            'name' => 'Design Work',
            'quantity' => 10,
            'amount' => 50,
        ]);

        expect($lineItem)->toHaveKey('id');
        expect($lineItem['name'])->toBe('Design Work');
        $lineItemId = $lineItem['id'];

        // Read
        $fetched = $this->nusii->lineItems()->get($lineItemId);
        expect($fetched['id'])->toBe($lineItemId);

        // Update
        $updated = $this->nusii->lineItems()->update($lineItemId, [
            'name' => 'Updated Design Work',
            'quantity' => 20,
        ]);
        expect($updated['name'])->toBe('Updated Design Work');

        // List by section
        $items = $this->nusii->lineItems()->listBySection($section['id']);
        expect($items)->toBeInstanceOf(PaginatedResponse::class);
        expect($items->totalCount)->toBeGreaterThanOrEqual(1);

        // Delete
        $this->nusii->lineItems()->delete($lineItemId);

        // Cleanup
        $this->nusii->sections()->delete($section['id']);
        $this->nusii->proposals()->delete($proposal['id']);
        $this->nusii->clients()->delete($client['id']);
    });
});

describe('Integration: Templates', function () {
    it('lists templates', function () {
        $templates = $this->nusii->templates()->list();

        expect($templates)->toBeInstanceOf(PaginatedResponse::class);
    });
});

describe('Integration: Proposal Activities', function () {
    it('lists proposal activities', function () {
        $activities = $this->nusii->proposalActivities()->list();

        expect($activities)->toBeInstanceOf(PaginatedResponse::class);
    });
});

describe('Integration: Users', function () {
    it('lists users', function () {
        $users = $this->nusii->users()->list();

        expect($users)->toBeInstanceOf(PaginatedResponse::class);
        expect($users->totalCount)->toBeGreaterThanOrEqual(1);
    });
});

describe('Integration: Themes', function () {
    it('lists themes', function () {
        $themes = $this->nusii->themes()->list();

        expect($themes)->toBeArray();
    });
});

describe('Integration: Webhook Endpoints', function () {
    it('creates and deletes a webhook endpoint', function () {
        // Create
        $webhook = $this->nusii->webhookEndpoints()->create([
            'target_url' => 'https://example.com/webhook-test-' . time(),
            'events' => ['proposal_accepted'],
        ]);

        expect($webhook)->toHaveKey('id');
        expect($webhook['target_url'])->toContain('https://example.com/webhook-test-');
        $webhookId = $webhook['id'];

        // Read
        $fetched = $this->nusii->webhookEndpoints()->get($webhookId);
        expect($fetched['id'])->toBe($webhookId);

        // List
        $list = $this->nusii->webhookEndpoints()->list();
        expect($list)->toBeInstanceOf(PaginatedResponse::class);

        // Delete
        $this->nusii->webhookEndpoints()->delete($webhookId);
    });
});
