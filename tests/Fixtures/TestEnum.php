<?php

namespace Olivermbs\LaravelEnumshare\Tests\Fixtures;

use Olivermbs\LaravelEnumshare\Attributes\Label;
use Olivermbs\LaravelEnumshare\Attributes\Meta;
use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend;

enum TestEnum: string
{
    use SharesWithFrontend;

    #[Label('Active Status')]
    #[Meta(['color' => 'green', 'icon' => 'check'])]
    case Active = 'active';

    #[Label('Inactive Status')]
    #[Meta(['color' => 'red', 'icon' => 'x'])]
    case Inactive = 'inactive';
}
