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

export interface EnumProxy {
  [key: string]: EnumEntry;
  name: string;
  entries: EnumEntry[];
  options: EnumOption[];
  keys(): string[];
  values(): (string | number)[];
  labels(): string[];
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
  };
  
  // Create proxy to handle dynamic property access
  return new Proxy(baseObject, {
    get(target: any, prop: string | symbol) {
      if (typeof prop === 'string' && entriesMap[prop]) {
        return entriesMap[prop];
      }
      return target[prop];
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

export default buildEnums;