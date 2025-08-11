<?php

namespace Olivermbs\LaravelEnumshare\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Meta
{
    public function __construct(
        public readonly array $data = []
    ) {}
}
