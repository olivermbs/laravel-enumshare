<?php

namespace Olivermbs\LaravelEnumshare\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Label
{
    public function __construct(
        string $text
    ) {
        $this->text = (string) $text;
    }
    
    public readonly string $text;
}
