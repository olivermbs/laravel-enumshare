<?php

namespace Olivermbs\LaravelEnumshare\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Olivermbs\LaravelEnumshare\Contracts\FrontendEnum;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;

class EnumAutoDiscovery
{
    public function __construct(
        protected array $paths = [],
        protected array $namespaces = [],
        protected array $cacheConfig = []
    ) {}

    public function discover(): array
    {
        if ($this->isCacheEnabled()) {
            return Cache::remember(
                $this->getCacheKey(),
                $this->getCacheTtl(),
                fn () => $this->performDiscovery()
            );
        }

        return $this->performDiscovery();
    }

    public function clearCache(): void
    {
        if ($this->isCacheEnabled()) {
            Cache::forget($this->getCacheKey());
        }
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
                if ($this->isValidFrontendEnum($enumClass) && $this->matchesNamespacePatterns($enumClass)) {
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

        // Extract enum definitions
        preg_match_all('/enum\s+(\w+)(?:\s*:\s*\w+)?\s+(?:implements\s+[^{]+)?\{/i', $content, $enumMatches);

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
                   $reflection->implementsInterface(FrontendEnum::class);
        } catch (ReflectionException) {
            return false;
        }
    }

    protected function matchesNamespacePatterns(string $enumClass): bool
    {
        if (empty($this->namespaces)) {
            return true;
        }

        foreach ($this->namespaces as $pattern) {
            if ($this->matchesPattern($enumClass, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function matchesPattern(string $className, string $pattern): bool
    {
        // Convert glob-style pattern to regex
        $regexPattern = str_replace(
            ['\\', '*', '?'],
            ['\\\\', '.*', '.'],
            $pattern
        );

        return (bool) preg_match('/^'.$regexPattern.'$/i', $className);
    }

    protected function isCacheEnabled(): bool
    {
        return $this->cacheConfig['enabled'] ?? false;
    }

    protected function getCacheKey(): string
    {
        return $this->cacheConfig['key'] ?? 'enumshare.discovered_enums';
    }

    protected function getCacheTtl(): int
    {
        return $this->cacheConfig['ttl'] ?? 3600;
    }
}
