// TypeScript runtime for Laravel Enumshare
// Proxy-free implementation with full type safety

type Backing = string | number;

export type EnumEntry<K extends string, V extends Backing | null, M extends Record<string, unknown>> = Readonly<{
  key: K;
  value: V;
  label: string;
  meta: M;
}>;

export type EnumOption<V extends Backing> = Readonly<{
  value: V;
  label: string;
}>;

export type EnumData<K extends string, V extends Backing, M extends Record<string, unknown>> = Readonly<{
  name: string;
  fqcn: string;
  backingType: string;
  entries: readonly EnumEntry<K, V | null, M>[];
  options: readonly EnumOption<V>[];
}>;

export type EnumObject<
  K extends string,
  V extends Backing,
  M extends Record<string, unknown>
> = Readonly<
  {
    name: string;
    entries: readonly EnumEntry<K, V | null, M>[];
    options: readonly EnumOption<V>[];
    keys(): readonly K[];
    values(): readonly (V | K)[];
    labels(): readonly string[];
    from(value: V | K | null | undefined): EnumEntry<K, V | null, M> | null;
    tryFrom(value: unknown): EnumEntry<K, V | null, M> | null;
    hasKey(key: unknown): key is K;
    hasValue(val: unknown): val is V | K;
  } & { [P in K]: EnumEntry<K, V | null, M> }
>;

/** Build a plain, frozen enum object. No Proxy. */
export function buildEnum<T extends Record<string, any>>(
  data: T
): EnumObject<
  T['entries'][number]['key'],
  T['entries'][number]['value'],
  T['entries'][number]['meta']
> {
  const obj = Object.create(null) as Record<string, unknown>;

  const entries = Object.freeze(data.entries.map((e: any) => Object.freeze({ ...e })));
  const options = Object.freeze(data.options.map((o: any) => Object.freeze({ ...o })));

  const byKey = new Map<string, any>();
  const byValue = new Map<any, any>();

  for (const e of entries) {
    byKey.set(e.key, e);
    byValue.set(e.value ?? e.key, e);
    Object.defineProperty(obj, e.key, {
      value: e,
      enumerable: true,
      configurable: false,
      writable: false,
    });
  }

  let _keys: readonly string[] | undefined;
  let _values: readonly any[] | undefined;
  let _labels: readonly string[] | undefined;

  const api = {
    name: data.name,
    entries,
    options,
    keys() {
      return (_keys ??= entries.map((e: any) => e.key));
    },
    values() {
      return (_values ??= entries.map((e: any) => e.value ?? e.key));
    },
    labels() {
      return (_labels ??= entries.map((e: any) => e.label));
    },
    from(value: any) {
      if (value == null) return null;
      return byValue.get(value) ?? null;
    },
    tryFrom(value: unknown) {
      if (byValue.has(value)) return byValue.get(value)!;
      if (typeof value === 'string') {
        if (byKey.has(value)) return byKey.get(value)!;
        const n = Number(value);
        if (!Number.isNaN(n) && byValue.has(n)) return byValue.get(n)!;
      }
      return null;
    },
    hasKey(k: unknown) {
      return typeof k === 'string' && byKey.has(k);
    },
    hasValue(v: unknown) {
      return byValue.has(v);
    },
  } as const;

  const full = Object.assign(obj, api);
  return Object.freeze(full) as any;
}

// Legacy compatibility functions
export function createEnumProxy<
  const K extends string,
  const V extends Backing,
  const M extends Record<string, unknown> = Record<string, unknown>
>(enumData: EnumData<K, V, M>): EnumObject<K, V, M> {
  return buildEnum(enumData);
}

export function buildEnums<
  const K extends string,
  const V extends Backing,
  const M extends Record<string, unknown> = Record<string, unknown>
>(manifest: Record<string, EnumData<K, V, M>>): Record<string, EnumObject<K, V, M>> {
  const enums: Record<string, EnumObject<K, V, M>> = {};

  for (const enumName in manifest) {
    if (manifest.hasOwnProperty(enumName)) {
      enums[enumName] = buildEnum(manifest[enumName]);
    }
  }

  return enums;
}

export default buildEnums;
