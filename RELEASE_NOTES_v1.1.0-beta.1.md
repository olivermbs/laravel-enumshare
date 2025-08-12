# 🚀 Laravel EnumShare v1.1.0-beta.1

## Major Refactor: Blade-Based TypeScript Generation

This release introduces a complete architectural overhaul, moving from runtime-based enum generation to **pure static TypeScript generation** using Laravel Blade templates.

## ⚡ Breaking Changes

- **Removed EnumRuntime.ts dependency** - No more runtime `buildEnum()` calls
- **No ES2015+ requirement** - Generated TypeScript is compatible with ES5+
- **Changed output structure** - Cleaner, more readable generated TypeScript
- **Zero build dependencies** - No external runtime libraries needed

## ✨ New Features

### 🎯 Smart Type Detection
- **Automatic PHP → TypeScript mapping** for meta properties
- **Mixed label support** - Handles both string and multilingual Record<string, string> types
- **Intelligent type inference** - Arrays, objects, primitives all properly typed

### 📚 Comprehensive Documentation
- **Full JSDoc comments** with usage examples for every method
- **Perfect IDE IntelliSense** - Hover over any property or method for help
- **Type-safe parameters** - TypeScript catches invalid usage at compile time

### 🛠️ Rich Utility Methods (10+ new methods)

#### Validation & Info
```typescript
TripStatus.isValid('saved')     // boolean - Check if value exists
TripStatus.hasKey('Saved')      // boolean - Check if key exists  
TripStatus.count               // number - Pre-computed total entries
```

#### Functional Programming
```typescript
TripStatus.random()            // Get random enum entry
TripStatus.filter(e => e.meta.color === 'green')  // Filter by predicate
TripStatus.map(e => e.label)   // Transform entries
TripStatus.find(e => e.value === 'saved')         // Find first match
TripStatus.some(e => e.meta.urgent)               // Test if any match
TripStatus.every(e => e.value !== null)           // Test if all match
```

## 🎨 Generated TypeScript Example

**Before (v1.0.0):**
```typescript
import { buildEnum } from './EnumRuntime';
const TripStatusData = { /* ... */ } as const;
export const TripStatus = buildEnum(TripStatusData);
```

**After (v1.1.0):**
```typescript
/**
 * TripStatus enum generated from App\Enums\TripStatus
 * 
 * @example
 * // Access enum entries
 * TripStatus.SAVED.label // "Trip Saved" 
 * 
 * // Validation and utilities
 * TripStatus.isValid('saved') // true
 * TripStatus.random() // Random entry
 */
export const TripStatus = {
  /** Trip Saved */
  SAVED: {
    key: 'SAVED',
    value: 'saved', 
    label: 'Trip Saved',
    meta: { color: 'gray', icon: 'save' }
  },
  
  // Collections
  entries: TripStatusEntries,
  options: TripStatusOptions,
  count: 3,
  
  // 10+ utility methods with full JSDoc
  isValid(value: unknown): boolean { /* ... */ },
  random(): TripStatusEntry { /* ... */ },
  // ... and more
} as const;
```

## 🚀 Performance Improvements

- **Zero runtime overhead** - All processing happens at build time
- **Smaller bundle size** - No runtime dependencies to ship
- **O(1) property access** - Direct object property access instead of Map lookups
- **Better tree-shaking** - Static exports enable better bundler optimization

## 🎯 Developer Experience

- **No setup required** - Just import and use, no configuration needed  
- **Perfect TypeScript integration** - Union types, literal types, full inference
- **Rich IDE support** - Auto-completion, hover docs, parameter hints
- **Type-safe operations** - Compile-time validation prevents runtime errors

## 🛠️ Migration Guide

### For Existing Users

1. **Remove EnumRuntime imports:**
   ```diff
   - import { TripStatus } from '@/enums/TripStatus';
   + import { TripStatus } from '@/enums/TripStatus';
   ```

2. **Update tsconfig.json** (optional - no longer required):
   ```diff
   - "target": "ES2015"
   + "target": "ES5"  // Or any target you prefer
   ```

3. **Re-export your enums:**
   ```bash
   php artisan enums:export
   ```

### API Compatibility

All existing method calls remain the same:
- `TripStatus.SAVED.value` ✅
- `TripStatus.from('saved')` ✅  
- `TripStatus.options` ✅
- `TripStatus.labels()` ✅

**Plus 10+ new utility methods!**

## 📦 Installation

```bash
composer require olivermbs/laravel-enumshare:^1.1.0-beta.1
```

## 🧪 Beta Notes

This is a beta release. While thoroughly tested, please report any issues at:
https://github.com/olivermbs/laravel-enumshare/issues

## 📚 Full Documentation

Updated documentation with all new features available in the [README](https://github.com/olivermbs/laravel-enumshare#readme).

---

**What's Next:** Planning v1.1.0 stable release with additional performance optimizations and Vue/React integration helpers.