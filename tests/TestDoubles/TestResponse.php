<?php

declare(strict_types=1);

namespace Tests\TestDoubles;

use GuzzleHttp\Psr7\Response;

class TestResponse
{
    public static function json(int $status, array $body, array $headers = []): Response
    {
        return new Response(
            $status,
            array_merge(['Content-Type' => 'application/json'], $headers),
            json_encode($body),
        );
    }

    public static function noContent(): Response
    {
        return new Response(204, [], '');
    }

    public static function resource(string $type, int $id, array $attributes): Response
    {
        return self::json(200, [
            'data' => [
                'id' => (string) $id,
                'type' => $type,
                'attributes' => $attributes,
            ],
        ]);
    }

    public static function collection(string $type, array $items, array $meta = []): Response
    {
        $data = array_map(fn (array $item) => [
            'id' => (string) $item['id'],
            'type' => $type,
            'attributes' => array_diff_key($item, ['id' => true]),
        ], $items);

        return self::json(200, [
            'data' => $data,
            'meta' => array_merge([
                'current_page' => 1,
                'next_page' => null,
                'prev_page' => null,
                'total_pages' => 1,
                'total_count' => count($items),
            ], $meta),
        ]);
    }

    public static function error(int $status, string $message): Response
    {
        return self::json($status, ['error' => $message]);
    }
}
