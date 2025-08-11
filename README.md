# Laravel EnumShare

[![Latest Version on Packagist](https://img.shields.io/packagist/v/37539998-olivermbs/laravel-enumshare.svg?style=flat-square)](https://packagist.org/packages/37539998-olivermbs/laravel-enumshare)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/37539998-olivermbs/laravel-enumshare/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/37539998-olivermbs/laravel-enumshare/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/37539998-olivermbs/laravel-enumshare/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/37539998-olivermbs/laravel-enumshare/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/37539998-olivermbs/laravel-enumshare.svg?style=flat-square)](https://packagist.org/packages/37539998-olivermbs/laravel-enumshare)

Export PHP Enums to TypeScript/Inertia with labels, metadata, and type-safe frontend access. Generate JSON manifests and TypeScript definitions for seamless enum sharing between Laravel backends and frontend applications.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-enumshare.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-enumshare)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require 37539998-olivermbs/laravel-enumshare
```

Publish the configuration file, TypeScript runtime, and Vite plugin:

```bash
php artisan vendor:publish --tag="enumshare-config"
php artisan vendor:publish --tag="enumshare-stubs"
php artisan vendor:publish --tag="enumshare-vite"
```

## Quickstart

### 1. Create Your Enum

Create an enum that implements the `FrontendEnum` contract and uses the `SharesWithFrontend` trait:

```php
<?php

namespace App\Enums;

use Olivermbs\LaravelEnumshare\Attributes\Label;
use Olivermbs\LaravelEnumshare\Attributes\Meta;
use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend;
use Olivermbs\LaravelEnumshare\Contracts\FrontendEnum;

enum TripStatus: string implements FrontendEnum
{
    use SharesWithFrontend;
    
    #[Label('Trip Saved')]
    #[Meta(['color' => 'gray', 'icon' => 'save'])]
    case Saved = 'saved';
    
    #[Label('Confirmed Trip')]
    #[Meta(['color' => 'green', 'icon' => 'check'])]
    case Confirmed = 'confirmed';
    
    case Cancelled = 'cancelled';
}
```

### 2. Configure Your Enums

Add your enums to the configuration file:

```php
// config/enumshare.php
return [
    'strategy' => 'export',
    'enums' => [
        App\Enums\TripStatus::class,
        App\Enums\UserRole::class,
    ],
    'export' => [
        'json_path' => resource_path('js/enums/enums.generated.json'),
        'types_path' => resource_path('js/enums/enums.generated.d.ts'),
        'locale' => null,
    ],
    'lang_namespace' => 'enums',
];
```

### 3. Setup Vite Integration (Optional but Recommended)

Add the Vite plugin to automatically regenerate enums during development:

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { laravelEnums } from './vite-plugin-laravel-enums.js';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    laravelEnums(),
  ],
});
```

### 4. Export Enums

Run the export command to generate JSON manifest and TypeScript definitions:

```bash
php artisan enums:export
```

This creates:
- `resources/js/enums/enums.generated.json` - The enum data
- `resources/js/enums/enums.generated.d.ts` - TypeScript definitions
- `resources/js/enums/{EnumName}.ts` - Individual importable enum files

With the Vite plugin, this happens automatically when you change enum files!

### 5. Use in Frontend

#### Option A: Direct Import (Recommended)

Import individual enums directly - no setup required:

```typescript
import { TripStatus } from '@/enums/TripStatus';

// Type-safe access with IntelliSense
console.log(TripStatus.Saved.value);        // 'saved'
console.log(TripStatus.Saved.label);        // 'Trip Saved'
console.log(TripStatus.Saved.meta);         // { color: 'gray', icon: 'save' }

// Utility methods
console.log(TripStatus.keys());              // ['Saved', 'Confirmed', 'Cancelled']
console.log(TripStatus.values());            // ['saved', 'confirmed', 'cancelled']
console.log(TripStatus.labels());            // ['Trip Saved', 'Confirmed Trip', 'Cancelled']

// Options for select dropdowns
console.log(TripStatus.options);             // [{ value: 'saved', label: 'Trip Saved' }, ...]

// Use in conditionals
if (order.status === TripStatus.Confirmed.value) {
    // Handle confirmed order
}
```

#### Option B: Bulk Import

For legacy code or if you prefer the original approach:

```typescript
import manifest from '@/enums/enums.generated.json';
import { buildEnums } from '@/enums/EnumRuntime';

export const Enums = buildEnums(manifest);
const { TripStatus } = Enums;

// Same API as Option A
console.log(TripStatus.Saved.value);
```

#### Option C: Helper Functions

Use the new helper functions for dynamic access:

```typescript
import manifest from '@/enums/enums.generated.json';
import { getEnum, getAllEnums } from '@/enums/EnumRuntime';

// Get a specific enum (provide manifest on first call)
const TripStatus = getEnum('TripStatus', manifest);

// Subsequent calls don't need manifest (cached)
const UserRole = getEnum('UserRole');

// Get all enums at once
const allEnums = getAllEnums(manifest);
```

## Features

### Direct Import Support

✨ **New in v2**: Import enums directly without setup boilerplate!

```typescript
// Just one import - no setup required
import { TripStatus } from '@/enums/TripStatus';
```

### Vite Integration

🔥 **Automatic regeneration** during development! The Vite plugin:

- **Watches enum files** - Auto-regenerates when you change PHP enums
- **Watches translations** - Updates when you change `lang/*/enums.php` files  
- **Hot Module Replacement** - Frontend updates instantly without browser refresh
- **Build integration** - Ensures enums are generated before production builds

```javascript
// vite.config.js - Just add the plugin!
laravelEnums() // That's it!
```

### Attributes

- **`@Label`**: Custom display labels for enum cases
- **`@Meta`**: Arbitrary metadata (colors, icons, descriptions, etc.)

### Label Resolution

Labels are resolved in this priority order:

1. `@Label` attribute on the enum case
2. Translation from `lang/en/enums.php` using `enums.{EnumName}.{CaseName}`
3. The enum case name as fallback

### TypeScript Integration

- Strict TypeScript definitions with literal unions
- IntelliSense support for all enum properties
- Proxy-based API for natural enum access
- Support for both backed and pure enums

### CLI Commands

```bash
# Export enums (run manually or via Vite plugin)
php artisan enums:export

# Export for specific locale
php artisan enums:export --locale=es

# Export all locales at once
php artisan enums:export-all-locales

# Discover enums (with autodiscovery enabled)
php artisan enums:discover
```

## Auto-Discovery

Instead of manually configuring each enum, you can enable auto-discovery to automatically find and register enums:

### Enable Auto-Discovery

```php
// config/enumshare.php
'autodiscovery' => [
    'enabled' => true,
    'paths' => [
        'app/Enums',
        'app/Domain/*/Enums',
    ],
    'namespaces' => [
        'App\\Enums\\*',
        'App\\Domain\\*\\Enums\\*',
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
    ],
],
```

### Discovery Commands

```bash
# Discover enums and show what was found
php artisan enums:discover

# Clear discovery cache and rediscover
php artisan enums:discover --clear

# Clear discovery cache only
php artisan enums:clear
```

### How It Works

1. **Path Scanning**: Scans configured directories for PHP files containing enums
2. **Interface Validation**: Only includes enums implementing `FrontendEnum` contract
3. **Namespace Filtering**: Applies glob-style namespace patterns for inclusion
4. **Caching**: Caches discovered enums for performance (configurable TTL)
5. **Combination**: Merges discovered enums with manually configured ones

### Environment Variables

```bash
# Enable/disable autodiscovery
ENUMSHARE_AUTODISCOVERY=true

# Enable/disable caching
ENUMSHARE_CACHE=true
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Oliver Smith](https://github.com/37539998+olivermbs)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
