<?php

namespace Atto\Framework\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class Service
{
    public function __construct(public ?string $name = null)
    {

    }
}