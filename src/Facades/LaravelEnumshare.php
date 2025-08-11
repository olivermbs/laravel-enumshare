<?php

namespace Oliver Smith\LaravelEnumshare\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Oliver Smith\LaravelEnumshare\LaravelEnumshare
 */
class LaravelEnumshare extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Oliver Smith\LaravelEnumshare\LaravelEnumshare::class;
    }
}
