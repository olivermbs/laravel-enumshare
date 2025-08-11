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
  backingType: 'int' | 'string' | string;
  entries: ReadonlyArray<EnumEntry<K, V | null, M>>;
  options: ReadonlyArray<EnumOption<V>>;
}>;

export type EnumObject<
  K extends string,
  V extends Backing,
  M extends Record<string, unknown>
> = Readonly<
  {
    name: string;
    entries: ReadonlyArray<EnumEntry<K, V | null, M>>;
    options: ReadonlyArray<EnumOption<V>>;
    keys(): ReadonlyArray<K>;
    values(): ReadonlyArray<V | K>;
    labels(): ReadonlyArray<string>;
    from(value: V | K | null | undefined): EnumEntry<K, V | null, M> | null;
    tryFrom(value: unknown): EnumEntry<K, V | null, M> | null;
    hasKey(key: unknown): key is K;
    hasValue(val: unknown): val is V | K;
  } & { [P in K]: EnumEntry<K, V | null, M> }
>;

/** Build a plain, frozen enum object. No Proxy. */
export function buildEnum<
  const K extends string,
  const V extends Backing,
  const M extends Record<string, unknown> = Record<string, unknown>
>(data: EnumData<K, V, M>): EnumObject<K, V, M> {
  const obj = Object.create(null) as Record<string, unknown>;

  const entries = Object.freeze(data.entries.map(e => Object.freeze({ ...e }))) as ReadonlyArray<EnumEntry<K, V | null, M>>;
  const options = Object.freeze(data.options.map(o => Object.freeze({ ...o }))) as ReadonlyArray<EnumOption<V>>;

  const byKey = new Map<K, EnumEntry<K, V | null, M>>();
  const byValue = new Map<V | K, EnumEntry<K, V | null, M>>();

  for (const e of entries) {
    byKey.set(e.key, e);
    byValue.set((e.value ?? e.key) as V | K, e);
    Object.defineProperty(obj, e.key, {
      value: e,
      enumerable: true,
      configurable: false,
      writable: false,
    });
  }

  let _keys: ReadonlyArray<K> | undefined;
  let _values: ReadonlyArray<V | K> | undefined;
  let _labels: ReadonlyArray<string> | undefined;

  const api = {
    name: data.name,
    entries,
    options,
    keys(): ReadonlyArray<K> {
      return (_keys ??= entries.map(e => e.key) as ReadonlyArray<K>);
    },
    values(): ReadonlyArray<V | K> {
      return (_values ??= entries.map(e => (e.value ?? e.key) as V | K));
    },
    labels(): ReadonlyArray<string> {
      return (_labels ??= entries.map(e => e.label));
    },
    from(value: V | K | null | undefined) {
      if (value == null) return null;
      return byValue.get(value as V | K) ?? null;
    },
    tryFrom(value: unknown) {
      if (byValue.has(value as V | K)) return byValue.get(value as V | K)!;
      if (typeof value === 'string') {
        if (byKey.has(value as K)) return byKey.get(value as K)!;
        const n = Number(value);
        if (!Number.isNaN(n) && byValue.has(n as V)) return byValue.get(n as V)!;
      }
      return null;
    },
    hasKey(k: unknown): k is K {
      return typeof k === 'string' && byKey.has(k as K);
    },
    hasValue(v: unknown): v is V | K {
      return byValue.has(v as V | K);
    },
  } as const;

  const full = Object.assign(obj, api);
  return Object.freeze(full) as EnumObject<K, V, M>;
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
