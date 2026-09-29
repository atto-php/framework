<?php

namespace Atto\Framework\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
readonly class Inject
{
    public function __construct(public string $service)
    {
    }
}