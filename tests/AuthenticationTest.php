<?php

declare(strict_types=1);

use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('Authentication & Headers', function () {
    it('sends the correct authorization header', function () {
        $mock = MockClient::create([
            TestResponse::collection('clients', []),
        ]);

        $nusii = new Nusii('my-secret-api-key', client: $mock->client());
        $nusii->clients()->list();

        $request = $mock->lastRequest();
        expect($request->getHeaderLine('Authorization'))->toBe('Token token=my-secret-api-key');
    });

    it('sends the correct content-type header', function () {
        $mock = MockClient::create([
            TestResponse::collection('clients', []),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->clients()->list();

        $request = $mock->lastRequest();
        expect($request->getHeaderLine('Accept'))->toBe('application/json');
    });

    it('sends the user-agent header', function () {
        $mock = MockClient::create([
            TestResponse::collection('clients', []),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $nusii->clients()->list();

        $request = $mock->lastRequest();
        expect($request->getHeaderLine('User-Agent'))->toBe('nusii-php/' . Nusii::VERSION);
    });

    it('uses the correct base URL', function () {
        $mock = MockClient::create([
            TestResponse::collection('clients', []),
        ]);

        $nusii = new Nusii('test-key', baseUrl: 'https://custom.nusii.com', client: $mock->client());
        $nusii->clients()->list();

        $request = $mock->lastRequest();
        expect((string) $request->getUri())->toStartWith('https://custom.nusii.com/api/v2/');
    });

    it('uses localhost for development', function () {
        $mock = MockClient::create([
            TestResponse::collection('clients', []),
        ]);

        $nusii = new Nusii('test-key', baseUrl: 'http://localhost:3000', client: $mock->client());
        $nusii->clients()->list();

        $request = $mock->lastRequest();
        expect((string) $request->getUri())->toStartWith('http://localhost:3000/api/v2/');
    });

    it('tracks rate limit remaining', function () {
        $mock = MockClient::create([
            TestResponse::json(200, [
                'data' => [],
                'meta' => ['current_page' => 1, 'total_pages' => 1, 'total_count' => 0],
            ], ['x-ratelimit-remaining' => '95']),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        expect($nusii->rateLimitRemaining)->toBeNull();

        $nusii->clients()->list();

        expect($nusii->rateLimitRemaining)->toBe(95);
    });
});
