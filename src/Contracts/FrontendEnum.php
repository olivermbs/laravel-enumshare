<?php

namespace Olivermbs\LaravelEnumshare\Contracts;

interface FrontendEnum
{
    public static function forFrontend(?string $locale = null): array;
}
