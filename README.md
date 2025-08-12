# Laravel EnumShare

[![Latest Version on Packagist](https://img.shields.io/packagist/v/37539998-olivermbs/laravel-enumshare.svg?style=flat-square)](https://packagist.org/packages/37539998-olivermbs/laravel-enumshare)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/37539998-olivermbs/laravel-enumshare/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/37539998-olivermbs/laravel-enumshare/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/37539998-olivermbs/laravel-enumshare/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/37539998-olivermbs/laravel-enumshare/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/37539998-olivermbs/laravel-enumshare.svg?style=flat-square)](https://packagist.org/packages/37539998-olivermbs/laravel-enumshare)

Export PHP Enums to TypeScript with labels, metadata, and type-safe frontend access. Generate TypeScript definitions for seamless enum sharing between Laravel backends and frontend applications.


## Installation

You can install the package via composer:

```bash
composer require 37539998-olivermbs/laravel-enumshare
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="enumshare-config"
```

## TypeScript Configuration

Ensure your `tsconfig.json` supports ES2015+ for the `Map` type:

```json
{
  "compilerOptions": {
    "target": "ES2015"
  }
}
```

## Quick Start

### 1. Create an Enum

Create an enum with the `SharesWithFrontend` trait:

```php
<?php

namespace App\Enums;

use Olivermbs\LaravelEnumshare\Attributes\ExportMethod;
use Olivermbs\LaravelEnumshare\Attributes\Label;
use Olivermbs\LaravelEnumshare\Attributes\Meta;
use Olivermbs\LaravelEnumshare\Attributes\TranslatedLabel;
use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend;
enum TripStatus: string
{
    use SharesWithFrontend;
    
    #[TranslatedLabel('trips.saved')]
    #[Meta(['color' => 'gray', 'icon' => 'save'])]
    case Saved = 'saved';
    
    #[Label('Confirmed Trip')]
    #[Meta(['color' => 'green', 'icon' => 'check'])]
    case Confirmed = 'confirmed';
    
    case Cancelled = 'cancelled';

    #[ExportMethod]
    public function isActive(): bool
    {
        return $this !== self::Cancelled;
    }
}
```

### 2. Configure Enums

Add enums to the config file:

```php
// config/enumshare.php
return [
    'enums' => [
        App\Enums\TripStatus::class,
        App\Enums\UserRole::class,
    ],
    'export' => [
        'path' => resource_path('js/enums'),
    ],
];
```

### 3. Auto-Regeneration (Optional)

**Vite Wayfinder (Recommended)**

```bash
npm install --save-dev @laravel/vite-plugin-wayfinder
```

```javascript
// vite.config.js
import { wayfinder } from '@laravel/vite-plugin-wayfinder';

export default defineConfig({
  plugins: [
    // ... other plugins
    wayfinder({
      command: 'php artisan enums:export',
      patterns: ['app/Enums/**/*.php', 'lang/*/enums.php'],
    }),
  ],
});
```

**Manual Watch**

```bash
php artisan enums:watch
```

### 4. Export and Use

```bash
php artisan enums:export
```

Import directly in your frontend:

```typescript
import { TripStatus } from '@/enums/TripStatus';

// Type-safe access with IntelliSense
console.log(TripStatus.Saved.value);        // 'saved'
console.log(TripStatus.Saved.label);        // 'Trip Saved'
console.log(TripStatus.Saved.meta);         // { color: 'gray', icon: 'save' }

// Utility methods
console.log(TripStatus.keys());             // ['Saved', 'Confirmed', 'Cancelled']
console.log(TripStatus.values());           // ['saved', 'confirmed', 'cancelled']
console.log(TripStatus.options);            // [{ value: 'saved', label: 'Trip Saved' }, ...]

// Use in conditionals
if (trip.status === TripStatus.Confirmed.value) {
    // Handle confirmed trip
}
```

## Features

- **Direct imports** - Import enums directly without setup boilerplate
- **Auto-regeneration** - Automatic updates during development with Vite Wayfinder
- **Attributes** - `@Label`, `@TranslatedLabel`, `@Meta`, `@ExportMethod` for rich enum data
- **Multi-locale** - Built-in translation support for international apps
- **TypeScript integration** - Strict definitions with IntelliSense support
- **Method export** - Export computed properties and business logic

## Commands

```bash
php artisan enums:export              # Export enums
php artisan enums:export --locale=es  # Export specific locale
php artisan enums:export-all-locales  # Export all locales
php artisan enums:watch               # Watch for changes
php artisan enums:discover            # Discover enums
```

## Translations

### TranslatedLabel Attribute

Use `@TranslatedLabel` for translation-based labels instead of hardcoded strings:

```php
enum OrderStatus: string
{
    use SharesWithFrontend;

    #[TranslatedLabel('orders.pending')]
    case Pending = 'pending';

    #[TranslatedLabel('orders.confirmed', ['status' => 'active'])] 
    case Confirmed = 'confirmed';

    #[Label('Cancelled')] // Mix with regular labels
    case Cancelled = 'cancelled';
}
```

### Multi-Locale Support

Configure multiple locales to export all translations:

```php
// config/enumshare.php
'export' => [
    'locales' => ['en', 'fr', 'es'],
],
```

Create translation files:

```php
// lang/en/orders.php
return [
    'pending' => 'Pending Order',
    'confirmed' => 'Confirmed :status Order',
];

// lang/fr/orders.php  
return [
    'pending' => 'Commande en attente',
    'confirmed' => 'Commande :status confirmée',
];
```

### Output Format

**Single locale** (when no `locales` configured):
```typescript
{
    label: "Pending Order"
}
```

**Multiple locales**:
```typescript  
{
    label: {
        en: "Pending Order",
        fr: "Commande en attente",
        es: "Pedido pendiente"
    }
}
```

## Method Export

Export method results as static properties with `@ExportMethod`:

### Basic Usage

```php
<?php

namespace App\Enums;

use Olivermbs\LaravelEnumshare\Attributes\ExportMethod;
use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend;
enum ContactType: int
{
    use SharesWithFrontend;

    case EMAIL = 1;
    case PHONE = 2;
    case SMS = 3;
    case POSTAL = 4;

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::PHONE => 'Phone',
            self::SMS => 'SMS',
            self::POSTAL => 'Postal Mail',
        };
    }

    #[ExportMethod]
    public function isInstant(): bool
    {
        return $this !== self::POSTAL;
    }

    #[ExportMethod('requiresPhoneNumber')]
    public function needsPhone(): bool
    {
        return match ($this) {
            self::PHONE, self::SMS => true,
            default => false,
        };
    }
}
```

### Generated TypeScript

The exported TypeScript will include method results as properties:

```typescript
// ContactType.ts
const ContactTypeData = {
  "entries": [
    {
      "key": "EMAIL",
      "value": 1,
      "label": "Email",
      "meta": {},
      "isInstant": true,
      "requiresPhoneNumber": false
    },
    {
      "key": "PHONE",
      "value": 2,
      "label": "Phone", 
      "meta": {},
      "isInstant": true,
      "requiresPhoneNumber": true
    },
    {
      "key": "POSTAL",
      "value": 4,
      "label": "Postal Mail",
      "meta": {},
      "isInstant": false,
      "requiresPhoneNumber": false
    }
  ]
} as const;
```

### Frontend Usage

Access method results as properties in your frontend code:

```typescript
import { ContactType } from '@/enums/ContactType';

// Access computed properties
console.log(ContactType.EMAIL.isInstant);           // true
console.log(ContactType.POSTAL.isInstant);          // false
console.log(ContactType.PHONE.requiresPhoneNumber); // true

// Use in conditional logic
const contactMethods = ContactType.entries.filter(method => 
    method.isInstant && !method.requiresPhoneNumber
);
// Returns: [EMAIL entry]

// React/Vue component example
const ContactForm = () => {
    const [selectedType, setSelectedType] = useState(ContactType.EMAIL.value);
    const selectedEntry = ContactType.from(selectedType);
    
    return (
        <div>
            <select onChange={(e) => setSelectedType(e.target.value)}>
                {ContactType.options.map(option => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            
            {selectedEntry?.requiresPhoneNumber && (
                <input type="tel" placeholder="Phone number required" />
            )}
            
            {selectedEntry?.isInstant && (
                <p>This contact method delivers instantly!</p>
            )}
        </div>
    );
};
```

### Custom Property Names

Use the optional parameter to customize the exported property name:

```php
#[ExportMethod('isUrgent')]
public function canHandleUrgentRequests(): bool
{
    return $this === self::PHONE || $this === self::EMAIL;
}
```

### Requirements

- Methods must be **public** and take **no parameters**
- Methods should return **serializable values** (bool, string, int, array, etc.)
- Methods that throw exceptions will be skipped during export
- Only methods with the `@ExportMethod` attribute are exported

### Use Cases

- **Business logic**: Export validation rules, permissions, or business constraints
- **UI helpers**: Color schemes, icons, display preferences
- **Computed properties**: Derived values based on enum state
- **Feature flags**: Enable/disable functionality per enum case

```php
enum UserRole: string
{
    use SharesWithFrontend;

    case ADMIN = 'admin';
    case MODERATOR = 'moderator'; 
    case USER = 'user';

    #[ExportMethod]
    public function canModerate(): bool
    {
        return $this !== self::USER;
    }

    #[ExportMethod('hasFullAccess')]
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    #[ExportMethod]
    public function getPermissionLevel(): int
    {
        return match($this) {
            self::ADMIN => 100,
            self::MODERATOR => 50,
            self::USER => 10,
        };
    }
}
```

## Auto-Discovery

Automatically find and register enums:

### Enable Auto-Discovery

```php
// config/enumshare.php
'autodiscovery' => [
    'enabled' => true,
    'paths' => [
        'app/Enums',
        'app/Domain/*/Enums',
    ],
],
```

### Discovery Commands

```bash
# Discover enums and show what was found (always fresh - no caching)
php artisan enums:discover
```

### How It Works

1. **Path Scanning**: Scans configured directories for PHP files containing enums
2. **Trait Validation**: Only includes enums using `SharesWithFrontend` trait
3. **Combination**: Merges discovered enums with manually configured ones

### Environment Variables

```bash
# Enable/disable autodiscovery
ENUMSHARE_AUTODISCOVERY=true
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
