<?php

declare(strict_types=1);

namespace Nusii\Resources;

class ThemeResource extends Resource
{
    /**
     * List all themes.
     *
     * Note: The themes endpoint returns a plain array, not JSON:API format.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function list(): array
    {
        return $this->nusii->sendRaw('GET', 'themes');
    }
}
