<?php

namespace Olivermbs\LaravelEnumshare\Commands;

use Illuminate\Console\Command;

class LaravelEnumshareCommand extends Command
{
    public $signature = 'laravel-enumshare';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
