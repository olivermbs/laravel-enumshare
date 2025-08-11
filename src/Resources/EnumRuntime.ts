// TypeScript runtime for Laravel Enumshare
// This file provides a Proxy-based API to work with exported enums

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
  backingType: 'string' | 'int' | null;
  entries: EnumEntry[];
  options: EnumOption[];
}

export interface EnumProxy extends Record<string, EnumEntry> {
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
  const baseObject = {
    name: enumData.name,
    entries: enumData.entries,
    options: enumData.options,
    keys: () => enumData.entries.map(entry => entry.key),
    values: () => enumData.entries.map(entry => entry.value ?? entry.key),
    labels: () => enumData.entries.map(entry => entry.label),
  } as any;

  // Add the from method explicitly to avoid conflicts
  const findEntryByValue = (value: string | number) => {
    // Handle null/undefined gracefully
    if (value === null || value === undefined) {
      return null;
    }
    
    try {
      const entry = enumData.entries.find(entry =>
        (entry.value !== null ? entry.value : entry.key) === value
      );
      return entry || null;
    } catch (error) {
      // Silently handle any errors and return null
      return null;
    }
  };

  baseObject.from = findEntryByValue;

  // Create proxy to handle dynamic property access
  return new Proxy(baseObject, {
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
  }) as EnumProxy;
}

export function buildEnums(manifest: Record<string, EnumData>): Record<string, EnumProxy> {
  const enums: Record<string, EnumProxy> = {};

  for (const [enumName, enumData] of Object.entries(manifest)) {
    enums[enumName] = createEnumProxy(enumData);
  }

  return enums;
}

let cachedEnums: Record<string, EnumProxy> | null = null;

export function getEnum<T extends EnumProxy = EnumProxy>(
  enumName: string,
  manifest?: Record<string, EnumData>
): T {
  if (!cachedEnums) {
    if (!manifest) {
      throw new Error('Manifest must be provided on first call to getEnum');
    }
    cachedEnums = buildEnums(manifest);
  }

  const enumProxy = cachedEnums[enumName];
  if (!enumProxy) {
    throw new Error(`Enum '${enumName}' not found in manifest`);
  }

  return enumProxy as unknown as T;
}

export function getAllEnums(manifest?: Record<string, EnumData>): Record<string, EnumProxy> {
  if (!cachedEnums) {
    if (!manifest) {
      throw new Error('Manifest must be provided on first call to getAllEnums');
    }
    cachedEnums = buildEnums(manifest);
  }

  return cachedEnums;
}

export default buildEnums;
