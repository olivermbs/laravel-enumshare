<?php

namespace Olivermbs\LaravelEnumshare\Tests\Fixtures;

use Olivermbs\LaravelEnumshare\Attributes\Meta;
use Olivermbs\LaravelEnumshare\Attributes\TranslatedLabel;
use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend;

enum MultilingualTestEnum: string
{
    use SharesWithFrontend;

    #[TranslatedLabel('status.active')]
    #[Meta(['color' => 'green', 'icon' => 'check'])]
    case Active = 'active';

    #[TranslatedLabel('status.inactive')]
    #[Meta(['color' => 'red', 'icon' => 'x'])]
    case Inactive = 'inactive';
}
