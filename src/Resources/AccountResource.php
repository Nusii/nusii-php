<?php

declare(strict_types=1);

namespace Nusii\Resources;

class AccountResource extends Resource
{
    public function me(): array
    {
        return $this->nusii->getResource('account/me');
    }
}
