<?php

declare(strict_types=1);

use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('ThemeResource', function () {
    it('lists themes', function () {
        $mock = MockClient::create([
            TestResponse::json(200, [
                ['id' => 1, 'name' => 'Classic'],
                ['id' => 2, 'name' => 'Modern'],
                ['id' => 3, 'name' => 'Minimal'],
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());
        $themes = $nusii->themes()->list();

        expect($themes)->toHaveCount(3);
        expect($themes[0]['name'])->toBe('Classic');
        expect($themes[1]['name'])->toBe('Modern');
        expect($themes[2]['name'])->toBe('Minimal');

        $request = $mock->lastRequest();
        expect($request->getMethod())->toBe('GET');
        expect((string) $request->getUri())->toContain('/api/v2/themes');
    });
});
