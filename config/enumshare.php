<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enums to Export
    |--------------------------------------------------------------------------
    |
    | List of enum class FQCNs that should be exported to the frontend.
    | Each enum must use the SharesWithFrontend trait.
    |
    */
    'enums' => [
        // App\Enums\TripStatus::class,
        // App\Enums\UserRole::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Path
    |--------------------------------------------------------------------------
    |
    | Path where the enum TypeScript files will be written.
    |
    */
    'path' => resource_path('js/Enums'),

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Default locale and additional locales for translations.
    | Set locale to null to use app()->getLocale().
    | Add locales to export multilingual enum labels.
    |
    */
    'locale' => null,
    'locales' => [
        // 'en',
        // 'fr',
        // 'es',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery
    |--------------------------------------------------------------------------
    |
    | Automatically discover enums in specified paths.
    | Scans for enums that use the SharesWithFrontend trait.
    |
    */
    'auto_discovery' => env('ENUMSHARE_AUTODISCOVERY', true),
    'auto_paths' => [
        'app/Enums',
        // 'app/Models/Enums',
        // 'src/Domain/*/Enums',
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Namespace
    |--------------------------------------------------------------------------
    |
    | Namespace for automatic label resolution when no Label attribute is present.
    | Labels resolve from: lang("{namespace}.{EnumShortName}.{CaseName}")
    |
    */
    'lang_namespace' => 'enums',
];
