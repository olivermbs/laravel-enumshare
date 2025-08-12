<?php

namespace Olivermbs\LaravelEnumshare\Support;

use Illuminate\Support\Facades\File;
use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;

class EnumAutoDiscovery
{
    public function __construct(
        protected array $paths = []
    ) {}

    public function discover(): array
    {
        return $this->performDiscovery();
    }

    public function clearCache(): void
    {
        // No cache to clear - discovery is always fresh
    }

    protected function performDiscovery(): array
    {
        $enums = [];

        foreach ($this->paths as $path) {
            $enums = array_merge($enums, $this->scanPath($path));
        }

        return array_unique($enums);
    }

    protected function scanPath(string $path): array
    {
        $fullPath = base_path($path);

        if (! File::isDirectory($fullPath)) {
            return [];
        }

        $enums = [];
        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($fullPath);

        foreach ($finder as $file) {
            $enumClasses = $this->extractEnumClassesFromFile($file->getRealPath());

            foreach ($enumClasses as $enumClass) {
                if ($this->isValidFrontendEnum($enumClass)) {
                    $enums[] = $enumClass;
                }
            }
        }

        return $enums;
    }

    protected function extractEnumClassesFromFile(string $filePath): array
    {
        $content = File::get($filePath);
        $enums = [];

        // Extract namespace
        preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches);
        $namespace = $namespaceMatches[1] ?? '';

        // Extract enum definitions - handle multiline declarations
        preg_match_all('/enum\s+(\w+)(?:\s*:\s*\w+)?(?:\s+implements\s+[^{]+)?\s*\{/ims', $content, $enumMatches);

        foreach ($enumMatches[1] as $enumName) {
            if ($namespace) {
                $enums[] = $namespace.'\\'.$enumName;
            } else {
                $enums[] = $enumName;
            }
        }

        return $enums;
    }

    protected function isValidFrontendEnum(string $enumClass): bool
    {
        if (! class_exists($enumClass)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($enumClass);

            return $reflection->isEnum() &&
                   in_array(SharesWithFrontend::class, $reflection->getTraitNames());
        } catch (ReflectionException) {
            return false;
        }
    }
}
