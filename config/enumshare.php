<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enums to Export
    |--------------------------------------------------------------------------
    |
    | List of enum class FQCNs that should be exported to the frontend.
    | Each enum must implement the FrontendEnum contract and use the
    | SharesWithFrontend trait.
    |
    */
    'enums' => [
        Tests\TestStatus::class,
        // App\Enums\TripStatus::class,
        // App\Enums\UserRole::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for build-time export strategy.
    |
    */
    'export' => [
        /*
        | Path where the enum files will be written
        */
        'path' => resource_path('js/enums'),

        /*
        | Default locale for label generation
        | Set to null to use app()->getLocale()
        */
        'locale' => null,

        /*
        | Locales to include when generating translations
        | Used by TranslatedLabel attributes to export all available translations
        | If empty, will default to just the current/default locale
        */
        'locales' => [
            // 'en',
            // 'fr',
            // 'es',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery Configuration
    |--------------------------------------------------------------------------
    |
    | Enable automatic discovery of enums that implement the FrontendEnum
    | contract. When enabled, the package will scan specified paths for
    | enums instead of relying solely on the 'enums' array above.
    |
    */
    'autodiscovery' => [
        /*
        | Enable or disable enum autodiscovery
        */
        'enabled' => env('ENUMSHARE_AUTODISCOVERY', true),

        /*
        | Paths to scan for enums (relative to base_path())
        | Uses PSR-4 namespace mapping to discover enums
        */
        'paths' => [
            'app/Enums',
            // 'app/Models/Enums',
            // 'src/Domain/*/Enums',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Language Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace used for automatic label resolution when no Label
    | attribute is present. Labels will be resolved from:
    | lang("{namespace}.{EnumShortName}.{CaseName}")
    |
    */
    'lang_namespace' => 'enums',
];
