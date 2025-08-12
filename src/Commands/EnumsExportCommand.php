<?php

namespace Olivermbs\LaravelEnumshare\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Olivermbs\LaravelEnumshare\Support\EnumRegistry;

class EnumsExportCommand extends Command
{
    protected $signature = 'enums:export
                            {--path= : Override the enum export path}
                            {--locale= : Override the locale for label generation}';

    protected $description = 'Export enums to TypeScript files';

    public function handle(EnumRegistry $registry): int
    {
        $locale = $this->option('locale') ?? config('enumshare.export.locale');
        $enumsDir = $this->option('path') ?? config('enumshare.export.path', resource_path('js/enums'));

        $this->info('Generating enum manifest...');

        $manifest = $registry->manifest($locale);

        if (empty($manifest)) {
            $this->warn('No enums found to export. Make sure enums are configured in config/enumshare.php');

            return self::SUCCESS;
        }

        $this->ensureDirectoryExists($enumsDir);

        $this->writeIndividualEnumFiles($manifest, $enumsDir);

        $enumCount = count($manifest);
        $this->info("✅ Exported {$enumCount} enum(s) to: {$enumsDir}");

        return self::SUCCESS;
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    protected function writeIndividualEnumFiles(array $manifest, string $enumsDir): void
    {
        foreach ($manifest as $enumName => $enumData) {
            $content = $this->generateIndividualEnumFile($enumName, $enumData);
            $filePath = "{$enumsDir}/{$enumName}.ts";
            File::put($filePath, $content);
        }
    }

    protected function generateIndividualEnumFile(string $enumName, array $enumData): string
    {
        // Ensure meta properties are objects, not arrays
        $enumData = $this->ensureMetaIsObject($enumData);

        return View::make('enumshare::enum', [
            'name' => $enumName,
            'fqcn' => $enumData['fqcn'],
            'backingType' => $enumData['backingType'],
            'entries' => $enumData['entries'],
            'options' => $enumData['options'],
        ])->render();
    }

    protected function ensureMetaIsObject(array $enumData): array
    {
        if (isset($enumData['entries'])) {
            foreach ($enumData['entries'] as &$entry) {
                if (isset($entry['meta']) && is_array($entry['meta']) && empty($entry['meta'])) {
                    $entry['meta'] = (object) [];
                }
            }
        }

        return $enumData;
    }
}
