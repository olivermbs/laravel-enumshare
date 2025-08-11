<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Export Strategy
    |--------------------------------------------------------------------------
    |
    | Determines how enums are shared with the frontend:
    | - 'export': Build-time export to JSON + TypeScript definitions
    | - 'inertia': Runtime sharing via Inertia middleware (future)
    | - 'both': Both strategies enabled (future)
    |
    */
    'strategy' => env('ENUMSHARE_STRATEGY', 'export'),

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
        | Path where the generated JSON manifest will be written
        */
        'json_path' => resource_path('js/enums/enums.generated.json'),

        /*
        | Path where the generated TypeScript definitions will be written
        */
        'types_path' => resource_path('js/enums/enums.generated.d.ts'),

        /*
        | Default locale for label generation
        | Set to null to use app()->getLocale()
        */
        'locale' => null,
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

        /*
        | Namespace patterns to include when scanning
        | Uses glob-style patterns: App\Enums\*, App\Domain\*\Enums\*
        */
        'namespaces' => [
            'App\\Enums\\*',
            // 'App\\Domain\\*\\Enums\\*',
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
