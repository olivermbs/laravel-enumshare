// This file is auto-generated. Do not edit manually.

{{-- Dynamic type detection --}}
@php
    // Helper function to map PHP types to TypeScript types (avoid redeclaration)
    if (!function_exists('phpToTsType')) {
        function phpToTsType($value) {
            if (is_null($value)) return 'null';
            if (is_bool($value)) return 'boolean';
            if (is_int($value)) return 'number';
            if (is_float($value)) return 'number';
            if (is_string($value)) return 'string';
            if (is_array($value)) {
                if (empty($value)) return 'Record<string, unknown>';
                // Check if it's an indexed array or associative
                if (array_keys($value) === range(0, count($value) - 1)) {
                    // Indexed array - get type of first element
                    $firstType = phpToTsType($value[0]);
                    return $firstType . '[]';
                } else {
                    // Associative array/object
                    return 'Record<string, any>';
                }
            }
            return 'unknown';
        }
    }

    // Analyze label types across ALL entries to handle mixed types
    $labelTypes = [];
    foreach ($entries as $entry) {
        if (is_array($entry['label'])) {
            $labelTypes[] = 'Record<string, string>';
        } else {
            $labelTypes[] = 'string';
        }
    }
    
    // Create union type if we have mixed label types
    $labelTypes = array_unique($labelTypes);
    $labelType = implode(' | ', $labelTypes);
    $isMultilingualLabel = in_array('Record<string, string>', $labelTypes);

    // Analyze meta types by checking all meta properties
    $metaTypes = [];
    foreach ($entries as $entry) {
        if (isset($entry['meta']) && is_array($entry['meta'])) {
            foreach ($entry['meta'] as $metaKey => $metaValue) {
                $tsType = phpToTsType($metaValue);
                if (!isset($metaTypes[$metaKey])) {
                    $metaTypes[$metaKey] = [];
                }
                $metaTypes[$metaKey][] = $tsType;
            }
        }
    }

    // Deduplicate and create union types
    foreach ($metaTypes as $key => $types) {
        $metaTypes[$key] = array_unique($types);
    }
@endphp

{{-- Generate specific meta type if we have meta properties --}}
@if(!empty($metaTypes))
export type {{ $name }}Meta = {
@foreach($metaTypes as $prop => $types)
  readonly {{ $prop }}: {!! implode(' | ', $types) !!};
@endforeach
};
@else
export type {{ $name }}Meta = Record<string, unknown>;
@endif

export type {{ $name }}Entry = {
  readonly key: {!! collect($entries)->pluck('key')->map(fn($key) => "'{$key}'")->implode(' | ') !!};
@if($backingType)
  readonly value: {{ $backingType === 'string' ? 'string' : 'number' }}{{ in_array(null, collect($entries)->pluck('value')->toArray()) ? ' | null' : '' }};
@else
  readonly value: null;
@endif
  readonly label: {!! $labelType !!};
  readonly meta: {{ $name }}Meta;
};

export type {{ $name }}Option = {
  readonly value: {{ $backingType ? ($backingType === 'string' ? 'string' : 'number') : 'string' }};
  readonly label: string;
};

{{-- Generate the enum object --}}
const {{ $name }}Entries: readonly {{ $name }}Entry[] = [
@foreach($entries as $entry)
  {
    key: '{{ $entry['key'] }}',
@if($entry['value'] !== null)
    value: {!! $backingType === 'string' ? "'{$entry['value']}'" : $entry['value'] !!},
@else
    value: null,
@endif
@if($isMultilingualLabel)
    label: {!! json_encode($entry['label']) !!},
@else
    label: '{{ addslashes($entry['label']) }}',
@endif
    meta: {!! json_encode($entry['meta']) !!},
  },
@endforeach
] as const;

const {{ $name }}Options: readonly {{ $name }}Option[] = [
@foreach($options as $option)
  {
    value: {!! $backingType === 'string' ? "'{$option['value']}'" : $option['value'] !!},
    label: '{{ addslashes($option['label']) }}',
  },
@endforeach
] as const;

{{-- Create individual entry constants --}}
@foreach($entries as $entry)
const {{ $entry['key'] }}: {{ $name }}Entry = {{ $name }}Entries[{{ $loop->index }}];
@endforeach

{{-- Generate the main enum object --}}
/**
 * {{ $name }} enum generated from {{ $fqcn }}
 * 
 * @example
 * // Access enum entries
@foreach($entries as $entry)
 * {{ $name }}.{{ $entry['key'] }}.label // "{!! is_array($entry['label']) ? (isset($entry['label']['en']) ? $entry['label']['en'] : reset($entry['label'])) : $entry['label'] !!}"
@endforeach
 * 
 * // Lookup by value
@if($backingType)
@foreach($entries as $entry)
 * {{ $name }}.from({{ $backingType === 'string' ? "'{$entry['value']}'" : $entry['value'] }}) // {{ $entry['key'] }} entry
@endforeach
@endif
 * 
 * // Get all options for dropdowns
 * {{ $name }}.options // [{ value, label }, ...]
 * 
 * // Get all labels
 * {{ $name }}.labels() // ["Draft", "Saved", ...]
 */
export const {{ $name }} = {
  /** The name of this enum */
  name: '{{ $name }}',
  /** The fully qualified class name in PHP */
  fqcn: '{{ $fqcn }}',
@if($backingType)
  /** The backing type of this enum */
  backingType: '{{ $backingType }}' as const,
@else
  backingType: null,
@endif

  // Entry constants
@foreach($entries as $entry)
  /** {{ is_array($entry['label']) ? (isset($entry['label']['en']) ? $entry['label']['en'] : reset($entry['label'])) : $entry['label'] }} */
  {{ $entry['key'] }},
@endforeach

  // Collections
  /** All enum entries with full data */
  entries: {{ $name }}Entries,
  /** Simplified options for dropdowns */
  options: {{ $name }}Options,
  /** Total number of entries */
  count: {{ count($entries) }},

  // Utility methods
  /**
   * Get all enum keys
   * @returns Array of all enum keys
   */
  keys(): readonly string[] {
    return {{ $name }}Entries.map(entry => entry.key);
  },

  /**
   * Get all enum values
   * @returns Array of all enum values
   */
  values(): readonly {{ $backingType ? ($backingType === 'string' ? 'string' : 'number') : 'string' }}[] {
@if($backingType)
    return {{ $name }}Entries.map(entry => entry.value!);
@else
    return {{ $name }}Entries.map(entry => entry.key);
@endif
  },

@if($isMultilingualLabel)
  /**
   * Get all enum labels
   * @param locale - Optional locale for multilingual labels
   * @returns Array of all enum labels
   */
  labels(locale?: string): readonly string[] {
    return {{ $name }}Entries.map(entry => {
      // Handle mixed string | Record<string, string> types
      if (typeof entry.label === 'string') {
        return entry.label;
      }
      // Handle Record<string, string> type
      if (locale && entry.label[locale]) {
        return entry.label[locale];
      }
      if (entry.label.en) {
        return entry.label.en;
      }
      const firstKey = Object.keys(entry.label)[0];
      return firstKey ? entry.label[firstKey] : '';
    });
  },
@else
  /**
   * Get all enum labels
   * @returns Array of all enum labels
   */
  labels(): readonly string[] {
    return {{ $name }}Entries.map(entry => entry.label as string);
  },
@endif

  /**
   * Find enum entry by value
   * @param value - The value to search for
   * @returns The matching enum entry or null if not found
   */
  from(value: {{ $backingType ? ($backingType === 'string' ? 'string' : 'number') : 'string' }} | null | undefined): {{ $name }}Entry | null {
    if (value == null) return null;
@if($backingType)
    return {{ $name }}Entries.find(entry => entry.value === value) ?? null;
@else
    return {{ $name }}Entries.find(entry => entry.key === value) ?? null;
@endif
  },

  /**
   * Find enum entry by key
   * @param key - The key to search for
   * @returns The matching enum entry or null if not found
   */
  fromKey(key: string | null | undefined): {{ $name }}Entry | null {
    if (key == null) return null;
    return {{ $name }}Entries.find(entry => entry.key === key) ?? null;
  },

  /**
   * Check if a value is valid for this enum
   * @param value - The value to validate
   * @returns True if the value exists in this enum
   */
  isValid(value: unknown): boolean {
@if($backingType)
    return {{ $name }}Entries.some(entry => entry.value === value);
@else
    return {{ $name }}Entries.some(entry => entry.key === value);
@endif
  },

  /**
   * Check if a key is valid for this enum
   * @param key - The key to validate
   * @returns True if the key exists in this enum
   */
  hasKey(key: string): boolean {
    return {{ $name }}Entries.some(entry => entry.key === key);
  },

  /**
   * Get a random enum entry
   * @returns A random enum entry
   */
  random(): {{ $name }}Entry {
    const randomIndex = Math.floor(Math.random() * {{ $name }}Entries.length);
    return {{ $name }}Entries[randomIndex];
  },

  /**
   * Filter enum entries by predicate
   * @param predicate - Function to test each entry
   * @returns Array of entries that pass the test
   */
  filter(predicate: (entry: {{ $name }}Entry) => boolean): {{ $name }}Entry[] {
    return {{ $name }}Entries.filter(predicate);
  },

  /**
   * Map over enum entries
   * @param mapper - Function to transform each entry
   * @returns Array of transformed values
   */
  map<T>(mapper: (entry: {{ $name }}Entry) => T): T[] {
    return {{ $name }}Entries.map(mapper);
  },

  /**
   * Find first entry matching predicate
   * @param predicate - Function to test each entry
   * @returns First matching entry or undefined
   */
  find(predicate: (entry: {{ $name }}Entry) => boolean): {{ $name }}Entry | undefined {
    return {{ $name }}Entries.find(predicate);
  },

  /**
   * Check if any entry matches predicate
   * @param predicate - Function to test each entry
   * @returns True if any entry matches
   */
  some(predicate: (entry: {{ $name }}Entry) => boolean): boolean {
    return {{ $name }}Entries.some(predicate);
  },

  /**
   * Check if all entries match predicate
   * @param predicate - Function to test each entry
   * @returns True if all entries match
   */
  every(predicate: (entry: {{ $name }}Entry) => boolean): boolean {
    return {{ $name }}Entries.every(predicate);
  },
} as const;