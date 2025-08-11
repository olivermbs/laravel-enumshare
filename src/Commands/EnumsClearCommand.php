<?php

namespace Olivermbs\LaravelEnumshare\Commands;

use Illuminate\Console\Command;
class EnumsClearCommand extends Command
{
    protected $signature = 'enums:clear';

    protected $description = 'No-op command (caching removed for simplicity)';

    public function handle(): int
    {
        $this->info('No cache to clear - enum discovery is always fresh!');
        return self::SUCCESS;
    }
}
