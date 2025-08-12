<?php

namespace Olivermbs\LaravelEnumshare\Support;

use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend;
use ReflectionClass;

class EnumRegistry
{
    public function __construct(
        protected array $enums = [],
        protected ?EnumAutoDiscovery $autoDiscovery = null
    ) {}

    public function manifest(?string $locale = null): array
    {
        $manifest = [];
        $allEnums = $this->getAllEnums();

        foreach ($allEnums as $enumClass) {
            if (! $this->isValidEnum($enumClass)) {
                continue;
            }

            $reflection = new ReflectionClass($enumClass);
            $shortName = $reflection->getShortName();

            $manifest[$shortName] = $enumClass::forFrontend($locale);
        }

        return $manifest;
    }

    protected function getAllEnums(): array
    {
        $configuredEnums = $this->enums;
        $discoveredEnums = [];

        if ($this->autoDiscovery && $this->isAutoDiscoveryEnabled()) {
            $discoveredEnums = $this->autoDiscovery->discover();
        }

        return array_unique(array_merge($configuredEnums, $discoveredEnums));
    }

    protected function isAutoDiscoveryEnabled(): bool
    {
        return config('enumshare.autodiscovery.enabled', false);
    }

    protected function isValidEnum(string $enumClass): bool
    {
        if (! class_exists($enumClass)) {
            return false;
        }

        $reflection = new ReflectionClass($enumClass);

        return $reflection->isEnum() &&
               in_array(SharesWithFrontend::class, $reflection->getTraitNames());
    }
}
