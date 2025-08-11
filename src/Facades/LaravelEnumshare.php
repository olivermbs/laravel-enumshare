<?php

namespace Olivermbs\LaravelEnumshare\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Olivermbs\LaravelEnumshare\LaravelEnumshare
 */
class LaravelEnumshare extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Olivermbs\LaravelEnumshare\LaravelEnumshare::class;
    }
}
