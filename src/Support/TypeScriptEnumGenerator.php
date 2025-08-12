<?php

namespace Olivermbs\LaravelEnumshare\Support;

class TypeScriptEnumGenerator
{
    public function __construct(
        protected TypeScriptTypeResolver $typeResolver
    ) {}

    public function generate(string $enumName, array $enumData, bool $exportTypes = false): string
    {
        $entries = $enumData['entries'];
        $options = $enumData['options'];
        $backingType = $enumData['backingType'];
        $fqcn = $enumData['fqcn'];

        $labelAnalysis = $this->typeResolver->analyzeLabelTypes($entries);
        $metaTypes = $this->typeResolver->analyzeMetaTypes($entries);

        return $this->buildTypeScriptFile(
            $enumName,
            $fqcn,
            $backingType,
            $entries,
            $options,
            $labelAnalysis,
            $metaTypes,
            $exportTypes
        );
    }

    protected function buildTypeScriptFile(
        string $enumName,
        string $fqcn,
        ?string $backingType,
        array $entries,
        array $options,
        array $labelAnalysis,
        array $metaTypes,
        bool $exportTypes = false
    ): string {
        $parts = [];

        $parts[] = '// This file is auto-generated. Do not edit manually.';
        $parts[] = '';
        
        if ($exportTypes) {
            $parts[] = $this->generateMetaType($enumName, $metaTypes);
            $parts[] = $this->generateEntryType($enumName, $entries, $backingType, $labelAnalysis['unionType']);
            $parts[] = $this->generateOptionType($enumName, $backingType);
        }
        
        $parts[] = $this->generateEntriesConstant($enumName, $entries, $backingType, $labelAnalysis['isMultilingual']);
        $parts[] = $this->generateLabelHelper($labelAnalysis['isMultilingual']);
        $parts[] = $this->generateMainEnumObject($enumName, $fqcn, $backingType, $entries, $labelAnalysis['isMultilingual'], $exportTypes);

        return implode("\n", $parts);
    }

    protected function generateMetaType(string $enumName, array $metaTypes): string
    {
        if (empty($metaTypes)) {
            return "export type {$enumName}Meta = Record<string, unknown>;";
        }

        $lines = ["export type {$enumName}Meta = {"];
        foreach ($metaTypes as $prop => $types) {
            $lines[] = "  readonly {$prop}: ".implode(' | ', $types).';';
        }
        $lines[] = '};';

        return implode("\n", $lines);
    }

    protected function generateEntryType(string $enumName, array $entries, ?string $backingType, string $labelType): string
    {
        $keyType = $this->typeResolver->generateKeyUnionType($entries);

        // The type resolver already handles null values properly
        if ($backingType) {
            $valueType = $this->typeResolver->determineValueType($backingType, $entries);
        } else {
            $valueType = 'null';
        }

        return "export type {$enumName}Entry = {\n".
               "  readonly key: {$keyType};\n".
               "  readonly value: {$valueType};\n".
               "  readonly label: {$labelType};\n".
               "  readonly meta: {$enumName}Meta;\n".
               '};';
    }

    protected function generateOptionType(string $enumName, ?string $backingType): string
    {
        if ($backingType) {
            $valueType = "{$enumName}Value";
        } else {
            $valueType = "{$enumName}Key"; // For pure enums, options use keys as values
        }

        return "export type {$enumName}Option = {\n".
               "  readonly value: {$valueType};\n".
               "  readonly label: string;\n".
               '};';
    }

    protected function generateEntriesConstant(string $enumName, array $entries, ?string $backingType, bool $isMultilingual): string
    {
        $lines = ["const ENTRIES = ["];

        foreach ($entries as $entry) {
            $lines[] = '  {';
            $lines[] = "    key: '{$entry['key']}',";

            if ($entry['value'] !== null) {
                $value = $backingType === 'string' ? "'{$entry['value']}'" : $entry['value'];
                $lines[] = "    value: {$value},";
            } else {
                $lines[] = '    value: null,';
            }

            if ($isMultilingual) {
                $lines[] = '    label: '.json_encode($entry['label']).',';
            } else {
                $lines[] = "    label: '".addslashes($entry['label'])."',";
            }

            $lines[] = '    meta: '.json_encode($entry['meta']).',';
            $lines[] = '  },';
        }

        $lines[] = "] as const satisfies readonly {$enumName}Entry[];";
        $lines[] = '';
        $lines[] = '// Derived constants from ENTRIES to avoid drift';
        $lines[] = "const KEYS: readonly {$enumName}Key[] = ENTRIES.map(e => e.key);";
        if ($backingType) {
            $lines[] = "const VALUES: readonly {$enumName}Value[] = ENTRIES.map(e => e.value).filter((v): v is {$enumName}Value => v !== null);";
            $lines[] = "const OPTIONS: ReadonlyArray<{$enumName}Option> = ENTRIES.filter((e): e is typeof ENTRIES[number] & { value: {$enumName}Value } => e.value !== null).map(e => ({ value: e.value, label: e.label }));";
        } else {
            $lines[] = "const VALUES: readonly {$enumName}Key[] = KEYS; // For pure enums, values are keys";
            $lines[] = "const OPTIONS: ReadonlyArray<{$enumName}Option> = ENTRIES.map(e => ({ value: e.key, label: e.label }));";
        }
        $lines[] = '';
        $lines[] = '// Precomputed lookup maps for O(1) access';
        $lines[] = "const BY_KEY = new Map<{$enumName}Key, typeof ENTRIES[number]>(ENTRIES.map(e => [e.key, e]));";
        if ($backingType) {
            $lines[] = "const BY_VALUE = new Map<{$enumName}Value, typeof ENTRIES[number]>(ENTRIES.filter(e => e.value !== null).map(e => [e.value as {$enumName}Value, e]));";
        }
        $lines[] = '';
        $lines[] = '// Individual constants for direct access';
        foreach ($entries as $index => $entry) {
            $lines[] = "const {$entry['key']}: typeof ENTRIES[number] = ENTRIES[{$index}];";
        }

        return implode("\n", $lines);
    }


    protected function generateMainEnumObject(string $enumName, string $fqcn, ?string $backingType, array $entries, bool $isMultilingual, bool $exportTypes = false): string
    {
        $lines = [];

        $lines[] = '/**';
        $lines[] = " * {$enumName} enum generated from {$fqcn}";
        $lines[] = ' * ';
        $lines[] = ' * @example';
        $lines[] = ' * // Access enum entries';

        foreach ($entries as $entry) {
            $label = is_array($entry['label'])
                ? ($entry['label']['en'] ?? reset($entry['label']))
                : $entry['label'];
            $lines[] = " * {$enumName}.{$entry['key']}.label // \"{$label}\"";
        }

        if ($backingType) {
            $lines[] = ' * ';
            $lines[] = ' * // Lookup by value';
            foreach ($entries as $entry) {
                $value = $backingType === 'string' ? "'{$entry['value']}'" : $entry['value'];
                $lines[] = " * {$enumName}.from({$value}) // {$entry['key']} entry";
            }
        }

        $lines[] = ' * ';
        $lines[] = ' * // Core utilities';
        $actualKeys = array_map(fn ($e) => "\"{$e['key']}\"", $entries);
        $lines[] = " * {$enumName}.keys // [".implode(', ', $actualKeys).'] - property, not method';

        if ($backingType) {
            // Only show non-null values for VALUES
            $actualValues = [];
            foreach ($entries as $e) {
                if ($e['value'] !== null) {
                    $actualValues[] = $backingType === 'string' ? "\"{$e['value']}\"" : $e['value'];
                }
            }
            $lines[] = " * {$enumName}.values // [".implode(', ', $actualValues).'] - excludes nulls';
        } else {
            $lines[] = " * {$enumName}.values // [".implode(', ', $actualKeys).'] - same as keys for pure enums';
        }

        $lines[] = " * {$enumName}.options // [{ value, label }, ...] - for dropdowns";
        $lines[] = " * {$enumName}.labels() // [...] - method for getting labels";
        $lines[] = ' * ';
        $lines[] = ' * // Validation (type guards)';
        $firstValue = $backingType ?
            ($backingType === 'string' ? "'{$entries[0]['value']}'" : $entries[0]['value']) :
            "'{$entries[0]['key']}'";
        $lines[] = " * {$enumName}.isValid({$firstValue}) // true";
        $lines[] = " * {$enumName}.hasKey('{$entries[0]['key']}') // true";
        $lines[] = ' */';

        // Constants already declared in generateEntriesConstant - no duplication needed
        $lines[] = '';
        
        if ($exportTypes) {
            $lines[] = '// Exported types for external use';
            $lines[] = "export type {$enumName}Key = typeof ENTRIES[number]['key'];";
            if ($backingType) {
                $lines[] = "export type {$enumName}Value = NonNullable<typeof ENTRIES[number]['value']>;";
            } else {
                $lines[] = "export type {$enumName}Value = {$enumName}Key;";
            }
        }
        $lines[] = '';
        $lines[] = "export const {$enumName} = {";
        $lines[] = "  name: '{$enumName}' as const,";
        $lines[] = "  fqcn: '".addslashes($fqcn)."' as const,";
        if ($backingType) {
            $lines[] = "  backingType: '{$backingType}' as const,";
        } else {
            $lines[] = '  backingType: null,';
        }

        $lines[] = '';
        $lines[] = '  // Entry constants';
        foreach ($entries as $entry) {
            $label = is_array($entry['label'])
                ? ($entry['label']['en'] ?? reset($entry['label']))
                : $entry['label'];
            $lines[] = "  /** {$label} */";
            $lines[] = "  {$entry['key']},";
        }

        $lines[] = '';
        $lines[] = '  // Collections (derived from ENTRIES)';
        $lines[] = '  entries: ENTRIES,';
        $lines[] = '  keys: KEYS,';
        $lines[] = '  values: VALUES,';
        $lines[] = '  options: OPTIONS,';
        $lines[] = '  count: ENTRIES.length,';

        $lines[] = $this->generateUtilityMethodImplementations($enumName, $backingType, $isMultilingual);

        $lines[] = '}';

        return implode("\n", $lines);
    }


    protected function generateLabelHelper(bool $isMultilingual): string
    {
        if (! $isMultilingual) {
            return '';
        }

        return implode("\n", [
            '',
            '// Locale-aware label resolution helper',
            'function resolveLabel(label: string | Record<string, string>, locale?: string): string {',
            '  if (typeof label === \'string\') return label;',
            '  if (locale && label[locale]) return label[locale];',
            '  if (label.en) return label.en;',
            '  const firstKey = Object.keys(label)[0];',
            '  return firstKey ? label[firstKey] : \'\';',
            '}',
        ]);
    }

    protected function generateUtilityMethodImplementations(string $enumName, ?string $backingType, bool $isMultilingual): string
    {
        $lines = [];

        // Maps are precomputed at module scope for performance

        // Lookup methods
        $lines[] = '';
        $lines[] = '  // Lookup methods';
        $lines[] = '  /**';
        $lines[] = '   * Find enum entry by value (O(1) lookup)';
        $lines[] = '   * @param value - The value to search for';
        $lines[] = '   * @returns The matching enum entry or null if not found';
        $lines[] = '   */';
        if ($backingType) {
            $valueType = $backingType === 'string' ? 'string' : 'number';
            $lines[] = "  from(value: {$valueType} | null | undefined): typeof ENTRIES[number] | null {";
            $lines[] = '    if (value == null) return null;';
            $lines[] = "    const v = value as {$enumName}Value;";
            $lines[] = '    return BY_VALUE.get(v) ?? null;';
        } else {
            $lines[] = "  from(value: string | null | undefined): typeof ENTRIES[number] | null {";
            $lines[] = '    if (value == null) return null;';
            $lines[] = "    const k = value as {$enumName}Key;";
            $lines[] = '    return BY_KEY.get(k) ?? null;';
        }
        $lines[] = '  },';

        $lines[] = '';
        $lines[] = '  /**';
        $lines[] = '   * Find enum entry by key (O(1) lookup)';
        $lines[] = '   * @param key - The key to search for';
        $lines[] = '   * @returns The matching enum entry or null if not found';
        $lines[] = '   */';
        $lines[] = "  fromKey(key: string | null | undefined): typeof ENTRIES[number] | null {";
        $lines[] = '    if (key == null) return null;';
        $lines[] = "    const k = key as {$enumName}Key;";
        $lines[] = '    return BY_KEY.get(k) ?? null;';
        $lines[] = '  },';

        // Type guard methods
        $lines[] = '';
        $lines[] = '  // Type guard methods';
        $lines[] = '  /**';
        $lines[] = '   * Check if a value is valid for this enum (type guard)';
        $lines[] = '   * @param value - The value to validate';
        $lines[] = '   * @returns True if the value exists in this enum';
        $lines[] = '   */';
        if ($backingType) {
            $lines[] = "  isValid(value: unknown): value is {$enumName}Value {";
            $lines[] = '    return typeof value === \''.($backingType === 'string' ? 'string' : 'number').'\' && BY_VALUE.has(value as '.$enumName.'Value);';
        } else {
            $lines[] = "  isValid(value: unknown): value is {$enumName}Key {";
            $lines[] = '    return typeof value === \'string\' && BY_KEY.has(value as '.$enumName.'Key);';
        }
        $lines[] = '  },';

        $lines[] = '';
        $lines[] = '  /**';
        $lines[] = '   * Check if a key is valid for this enum (type guard)';
        $lines[] = '   * @param key - The key to validate';
        $lines[] = '   * @returns True if the key exists in this enum';
        $lines[] = '   */';
        $lines[] = "  hasKey(key: unknown): key is {$enumName}Key {";
        $lines[] = '    return typeof key === \'string\' && BY_KEY.has(key as '.$enumName.'Key);';
        $lines[] = '  },';

        // Label method
        if ($isMultilingual) {
            $lines[] = '';
            $lines[] = '  /**';
            $lines[] = '   * Get all enum labels with locale resolution';
            $lines[] = '   * @param locale - Optional locale for multilingual labels';
            $lines[] = '   * @returns Array of all enum labels';
            $lines[] = '   */';
            $lines[] = '  labels(locale?: string): readonly string[] {';
            $lines[] = '    return ENTRIES.map(entry => resolveLabel(entry.label, locale));';
            $lines[] = '  },';
        } else {
            $lines[] = '';
            $lines[] = '  /**';
            $lines[] = '   * Get all enum labels';
            $lines[] = '   * @returns Array of all enum labels';
            $lines[] = '   */';
            $lines[] = '  labels(): readonly string[] {';
            $lines[] = '    return ENTRIES.map(entry => entry.label);';
            $lines[] = '  },';
        }

        return implode("\n", $lines);
    }
}
