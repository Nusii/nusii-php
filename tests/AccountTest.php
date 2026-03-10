<?php

declare(strict_types=1);

use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('AccountResource', function () {
    it('fetches the current account', function () {
        $mock = MockClient::create([
            TestResponse::resource('accounts', 1, [
                'email' => 'owner@example.com',
                'name' => 'Acme Corp',
                'subdomain' => 'acme',
                'web' => 'https://acme.com',
                'currency' => 'USD',
                'pdf_page_size' => 'A4',
                'locale' => 'en',
                'address' => '123 Main St',
                'address_state' => 'CA',
                'postcode' => '90210',
                'city' => 'Los Angeles',
                'telephone' => '555-1234',
                'default_theme' => 'classic',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $account = $nusii->accounts()->me();

        expect($account['id'])->toBe(1);
        expect($account['email'])->toBe('owner@example.com');
        expect($account['name'])->toBe('Acme Corp');
        expect($account['subdomain'])->toBe('acme');
        expect($account['currency'])->toBe('USD');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('GET');
        expect((string) $request->getUri())->toContain('/api/v2/account/me');
    });
});
