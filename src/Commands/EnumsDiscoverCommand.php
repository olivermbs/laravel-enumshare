<?php

namespace Olivermbs\LaravelEnumshare\Commands;

use Illuminate\Console\Command;
use Olivermbs\LaravelEnumshare\Support\EnumAutoDiscovery;

class EnumsDiscoverCommand extends Command
{
    protected $signature = 'enums:discover';

    protected $description = 'Discover enums that use the SharesWithFrontend trait';

    public function handle(): int
    {
        if (! config('enumshare.autodiscovery.enabled', false)) {
            $this->error('Enum autodiscovery is not enabled. Enable it in config/enumshare.php');

            return self::FAILURE;
        }

        $discovery = new EnumAutoDiscovery(
            config('enumshare.autodiscovery.paths', [])
        );

        $this->info('Discovering enums...');

        $discoveredEnums = $discovery->discover();

        if (empty($discoveredEnums)) {
            $this->warn('No enums found that use the SharesWithFrontend trait.');
            $this->line('Make sure your enums:');
            $this->line('  - Use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend trait');
            $this->line('  - Are located in the configured paths');

            return self::SUCCESS;
        }

        $this->info('Found '.count($discoveredEnums).' enum(s):');

        foreach ($discoveredEnums as $enum) {
            $this->line("  - {$enum}");
        }

        return self::SUCCESS;
    }
}
