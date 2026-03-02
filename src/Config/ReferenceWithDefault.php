<?php

declare(strict_types=1);

namespace Atto\Framework\Config;

final class ReferenceWithDefault
{
    public function __construct(public readonly string $to, public readonly mixed $default)
    {

    }
}