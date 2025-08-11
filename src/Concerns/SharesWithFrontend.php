<?php

namespace Olivermbs\LaravelEnumshare\Concerns;

use BackedEnum;
use Olivermbs\LaravelEnumshare\Attributes\Label;
use Olivermbs\LaravelEnumshare\Attributes\Meta;
use ReflectionClass;
use ReflectionClassConstant;

trait SharesWithFrontend
{
    public static function forFrontend(?string $locale = null): array
    {
        $reflection = new ReflectionClass(static::class);
        $enumName = $reflection->getShortName();

        $isBacked = $reflection->implementsInterface(BackedEnum::class);
        $backingType = null;

        if ($isBacked && count(static::cases()) > 0) {
            $firstCase = static::cases()[0];
            $backingType = is_string($firstCase->value) ? 'string' : 'int';
        }

        $entries = [];
        $options = [];

        foreach (static::cases() as $case) {
            $caseReflection = $reflection->getReflectionConstant($case->name);

            $label = static::resolveLabel($case, $caseReflection, $enumName, $locale);
            $meta = static::resolveMeta($caseReflection);

            $entry = [
                'key' => $case->name,
                'value' => $isBacked ? $case->value : null,
                'label' => $label,
                'meta' => $meta,
            ];

            $entries[] = $entry;
            $options[] = [
                'value' => $isBacked ? $case->value : $case->name,
                'label' => $label,
            ];
        }

        return [
            'name' => $enumName,
            'fqcn' => static::class,
            'backingType' => $backingType,
            'entries' => $entries,
            'options' => $options,
        ];
    }

    protected static function resolveLabel($case, ReflectionClassConstant $reflection, string $enumName, ?string $locale): string
    {
        $labelAttributes = $reflection->getAttributes(Label::class);

        if (! empty($labelAttributes)) {
            return $labelAttributes[0]->newInstance()->text;
        }

        $locale = $locale ?: app()->getLocale();
        $langNamespace = config('enumshare.lang_namespace', 'enums');
        $langKey = "{$langNamespace}.{$enumName}.{$case->name}";

        $translation = trans($langKey, [], $locale);

        if ($translation !== $langKey) {
            return $translation;
        }

        return $case->name;
    }

    protected static function resolveMeta(ReflectionClassConstant $reflection): array
    {
        $metaAttributes = $reflection->getAttributes(Meta::class);

        if (! empty($metaAttributes)) {
            return $metaAttributes[0]->newInstance()->data;
        }

        return [];
    }
}
