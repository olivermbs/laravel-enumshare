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

Publish the configuration file and TypeScript runtime:

```bash
php artisan vendor:publish --tag="enumshare-config"
php artisan vendor:publish --tag="enumshare-stubs"
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

### 3. Export Enums

Run the export command to generate JSON manifest and TypeScript definitions:

```bash
php artisan enums:export
```

This creates:
- `resources/js/enums/enums.generated.json` - The enum data
- `resources/js/enums/enums.generated.d.ts` - TypeScript definitions

### 4. Use in Frontend

Import and use your enums in TypeScript/JavaScript:

```typescript
import manifest from '@/enums/enums.generated.json';
import { buildEnums } from '@/enums/EnumRuntime';

export const Enums = buildEnums(manifest);
const { TripStatus } = Enums;

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

## Features

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

### CLI Options

```bash
# Override paths
php artisan enums:export --path=resources/js/custom/enums.json --types=resources/js/custom/types.d.ts

# Export for specific locale
php artisan enums:export --locale=es
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
