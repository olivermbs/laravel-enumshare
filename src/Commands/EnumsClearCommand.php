<?php

namespace Olivermbs\LaravelEnumshare\Commands;

use Illuminate\Console\Command;
use Olivermbs\LaravelEnumshare\Support\EnumAutoDiscovery;

class EnumsClearCommand extends Command
{
    protected $signature = 'enums:clear';

    protected $description = 'Clear cached discovered enums';

    public function handle(): int
    {
        if (! config('enumshare.autodiscovery.enabled', false)) {
            $this->error('Enum autodiscovery is not enabled. Enable it in config/enumshare.php');

            return self::FAILURE;
        }

        $discovery = new EnumAutoDiscovery(
            config('enumshare.autodiscovery.paths', []),
            config('enumshare.autodiscovery.namespaces', []),
            config('enumshare.autodiscovery.cache', [])
        );

        $discovery->clearCache();

        $this->info('Cleared cached discovered enums.');

        return self::SUCCESS;
    }
}
