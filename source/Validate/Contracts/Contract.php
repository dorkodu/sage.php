<?php

declare(strict_types=1);

namespace Sage\Validate\Contracts;

abstract class Contract
{
    public function name(): string
    {
        return static::class;
    }
}

