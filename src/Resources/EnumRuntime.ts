// TypeScript runtime for Laravel Enumshare
// This file provides a Proxy-based API to work with exported enums

/* eslint-disable @typescript-eslint/no-explicit-any */
declare const Proxy: any;

export interface EnumEntry {
  key: string;
  value: string | number | null;
  label: string;
  meta: Record<string, any>;
}

export interface EnumOption {
  value: string | number;
  label: string;
}

export interface EnumData {
  name: string;
  fqcn: string;
  backingType: string | null;
  entries: EnumEntry[];
  options: EnumOption[];
}

export interface EnumProxy {
  [key: string]: EnumEntry | any;
  name: string;
  entries: EnumEntry[];
  options: EnumOption[];
  keys(): string[];
  values(): (string | number)[];
  labels(): string[];
  from(value: string | number): EnumEntry | null;
}

export function createEnumProxy(enumData: EnumData): EnumProxy {
  const entriesMap: Record<string, EnumEntry> = {};

  // Build entries map
  for (const entry of enumData.entries) {
    entriesMap[entry.key] = entry;
  }

  // Base object with metadata and methods
  const baseObject: any = {
    name: enumData.name,
    entries: enumData.entries,
    options: enumData.options,
    keys: () => enumData.entries.map(entry => entry.key),
    values: () => enumData.entries.map(entry => entry.value ?? entry.key),
    labels: () => enumData.entries.map(entry => entry.label),
  };

  // Add the from method explicitly to avoid conflicts
  baseObject.from = (value: string | number) => {
    // Handle null/undefined gracefully
    if (value === null || value === undefined) {
      return null;
    }
    
    try {
      // Use a more compatible approach for older TypeScript versions
      for (let i = 0; i < enumData.entries.length; i++) {
        const entry = enumData.entries[i];
        if ((entry.value !== null ? entry.value : entry.key) === value) {
          return entry;
        }
      }
      return null;
    } catch (error) {
      // Silently handle any errors and return null
      return null;
    }
  };

  // Create proxy to handle dynamic property access
  return new Proxy(baseObject as EnumProxy, {
    get(target: any, prop: string | symbol) {
      try {
        if (typeof prop === 'string' && entriesMap[prop]) {
          return entriesMap[prop];
        }
        return target[prop];
      } catch (error) {
        // Silently handle any proxy errors
        return undefined;
      }
    },

    has(target: any, prop: string | symbol) {
      if (typeof prop === 'string' && entriesMap[prop]) {
        return true;
      }
      return prop in target;
    },

    ownKeys(target: any) {
      return [...Object.keys(target), ...Object.keys(entriesMap)];
    },

    getOwnPropertyDescriptor(target: any, prop: string | symbol) {
      if (typeof prop === 'string' && entriesMap[prop]) {
        return {
          enumerable: true,
          configurable: true,
          value: entriesMap[prop],
        };
      }
      return Object.getOwnPropertyDescriptor(target, prop);
    },
  });
}

export function buildEnums(manifest: Record<string, EnumData>): Record<string, EnumProxy> {
  const enums: Record<string, EnumProxy> = {};

  for (const enumName in manifest) {
    if (manifest.hasOwnProperty(enumName)) {
      enums[enumName] = createEnumProxy(manifest[enumName]);
    }
  }

  return enums;
}

export default buildEnums;
