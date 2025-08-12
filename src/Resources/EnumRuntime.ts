// TypeScript runtime for Laravel Enumshare
// Proxy-free implementation with full type safety

type Backing = string | number;

export type BackingType = 'int' | 'string';

export type EnumEntry<K extends string, V extends Backing | null, M extends Record<string, unknown>, X extends Record<string, unknown> = {}> = Readonly<{
  key: K;
  value: V;
  label: string | Readonly<Record<string, string>>;
  meta: M;
} & X>;

export type EnumOption<V extends Backing> = Readonly<{
  value: V;
  label: string;
}>;

export type EnumData<K extends string, V extends Backing, M extends Record<string, unknown>, X extends Record<string, unknown> = {}> = Readonly<{
  name: string;
  fqcn: string;
  backingType: BackingType;
  entries: readonly EnumEntry<K, V | null, M, X>[];
  options: readonly EnumOption<V>[];
}>;

export type EnumObject<
  K extends string,
  V extends Backing,
  M extends Record<string, unknown>,
  X extends Record<string, unknown> = {}
> = Readonly<
  {
    name: string;
    entries: readonly EnumEntry<K, V | null, M, X>[];
    options: readonly EnumOption<V>[];
    keys(): readonly K[];
    values(): readonly (V | K)[];
    labels(locale?: string): readonly string[];
    from(value: V | K | null | undefined): EnumEntry<K, V | null, M, X> | null;
  } & { [P in K]: EnumEntry<K, V | null, M, X> }
>;

/** Build a plain, frozen enum object. No Proxy. */
export function buildEnum<K extends string, V extends Backing, M extends Record<string, unknown>, X extends Record<string, unknown> = {}>(
  data: EnumData<K, V, M, X>
): EnumObject<K, V, M, X>;
export function buildEnum<T extends Record<string, any>>(
  data: T
): EnumObject<
  T['entries'][number]['key'],
  T['entries'][number]['value'],
  T['entries'][number]['meta']
>;
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

  const api = {
    name: data.name,
    entries,
    options,
    keys() {
      return (_keys ??= Object.freeze(entries.map((e: any) => e.key)));
    },
    values() {
      return (_values ??= Object.freeze(entries.map((e: any) => e.value ?? e.key)));
    },
    labels(locale?: string) {
      return Object.freeze(entries.map((e: any) => {
        if (typeof e.label === 'string') {
          return e.label;
        }
        if (locale && e.label[locale]) {
          return e.label[locale];
        }
        if (e.label.en) {
          return e.label.en;
        }
        const firstKey = Object.keys(e.label)[0];
        return firstKey ? e.label[firstKey] : '';
      }));
    },
    from(value: any) {
      if (value == null) return null;
      return byValue.get(value) ?? null;
    },
  } as const;

  const full = Object.assign(obj, api);
  return Object.freeze(full) as any;
}


export function buildEnums<
  const K extends string,
  const V extends Backing,
  const M extends Record<string, unknown> = Record<string, unknown>,
  const X extends Record<string, unknown> = {}
>(manifest: Record<string, EnumData<K, V, M, X>>): Record<string, EnumObject<K, V, M, X>> {
  const enums: Record<string, EnumObject<K, V, M, X>> = {};

  for (const enumName in manifest) {
    if (Object.prototype.hasOwnProperty.call(manifest, enumName)) {
      enums[enumName] = buildEnum(manifest[enumName]);
    }
  }

  return enums;
}

