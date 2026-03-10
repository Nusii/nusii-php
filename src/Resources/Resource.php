<?php

declare(strict_types=1);

namespace Nusii\Resources;

use Nusii\Nusii;

abstract class Resource
{
    public function __construct(
        protected readonly Nusii $nusii,
    ) {}
}
