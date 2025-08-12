<?php

use Olivermbs\LaravelEnumshare\Attributes\Label;
use Olivermbs\LaravelEnumshare\Attributes\Meta;
use Olivermbs\LaravelEnumshare\Attributes\TranslatedLabel;
use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend;
use Olivermbs\LaravelEnumshare\Support\EnumRegistry;
use Olivermbs\LaravelEnumshare\Tests\TestCase;

enum TripStatus: string
{
    use SharesWithFrontend;

    #[Label('Trip Saved')]
    #[Meta(['color' => 'gray', 'icon' => 'save'])]
    case Saved = 'saved';

    #[Label('Confirmed Trip')]
    #[Meta(['color' => 'green'])]
    case Confirmed = 'confirmed';

    case Cancelled = 'cancelled';
}

enum UserRole
{
    use SharesWithFrontend;

    case Admin;
    case User;
    case Guest;
}

enum OrderStatus: string
{
    use SharesWithFrontend;

    #[TranslatedLabel('orders.pending')]
    case Pending = 'pending';

    #[TranslatedLabel('orders.confirmed', ['status' => 'active'])]
    case Confirmed = 'confirmed';

    #[Label('Cancelled')]
    case Cancelled = 'cancelled';
}

class EnumExportTest extends TestCase
{
    public function test_backed_enum_generates_correct_structure(): void
    {
        $result = TripStatus::forFrontend();

        expect($result)
            ->toHaveKeys(['name', 'fqcn', 'backingType', 'entries', 'options'])
            ->and($result['name'])->toBe('TripStatus')
            ->and($result['fqcn'])->toBe(TripStatus::class)
            ->and($result['backingType'])->toBe('string')
            ->and($result['entries'])->toHaveCount(3)
            ->and($result['options'])->toHaveCount(3);
    }

    public function test_pure_enum_generates_correct_structure(): void
    {
        $result = UserRole::forFrontend();

        expect($result)
            ->toHaveKeys(['name', 'fqcn', 'backingType', 'entries', 'options'])
            ->and($result['name'])->toBe('UserRole')
            ->and($result['fqcn'])->toBe(UserRole::class)
            ->and($result['backingType'])->toBeNull()
            ->and($result['entries'])->toHaveCount(3)
            ->and($result['options'])->toHaveCount(3);
    }

    public function test_entry_structure_includes_all_fields(): void
    {
        $result = TripStatus::forFrontend();
        $savedEntry = collect($result['entries'])->firstWhere('key', 'Saved');

        expect($savedEntry)
            ->toHaveKeys(['key', 'value', 'label', 'meta'])
            ->and($savedEntry['key'])->toBe('Saved')
            ->and($savedEntry['value'])->toBe('saved')
            ->and($savedEntry['label'])->toBe('Trip Saved')
            ->and($savedEntry['meta'])->toBe(['color' => 'gray', 'icon' => 'save']);
    }

    public function test_options_structure_for_backed_enum(): void
    {
        $result = TripStatus::forFrontend();
        $savedOption = collect($result['options'])->firstWhere('value', 'saved');

        expect($savedOption)
            ->toHaveKeys(['value', 'label'])
            ->and($savedOption['value'])->toBe('saved')
            ->and($savedOption['label'])->toBe('Trip Saved');
    }

    public function test_options_structure_for_pure_enum(): void
    {
        $result = UserRole::forFrontend();
        $adminOption = collect($result['options'])->firstWhere('value', 'Admin');

        expect($adminOption)
            ->toHaveKeys(['value', 'label'])
            ->and($adminOption['value'])->toBe('Admin')
            ->and($adminOption['label'])->toBe('Admin');
    }

    public function test_label_resolution_with_attributes(): void
    {
        $result = TripStatus::forFrontend();
        $savedEntry = collect($result['entries'])->firstWhere('key', 'Saved');

        expect($savedEntry['label'])->toBe('Trip Saved');
    }

    public function test_label_resolution_fallback_to_case_name(): void
    {
        $result = TripStatus::forFrontend();
        $cancelledEntry = collect($result['entries'])->firstWhere('key', 'Cancelled');

        expect($cancelledEntry['label'])->toBe('Cancelled');
    }

    public function test_meta_resolution_with_attributes(): void
    {
        $result = TripStatus::forFrontend();
        $savedEntry = collect($result['entries'])->firstWhere('key', 'Saved');

        expect($savedEntry['meta'])->toBe(['color' => 'gray', 'icon' => 'save']);
    }

    public function test_meta_resolution_fallback_to_empty_array(): void
    {
        $result = TripStatus::forFrontend();
        $cancelledEntry = collect($result['entries'])->firstWhere('key', 'Cancelled');

        expect($cancelledEntry['meta'])->toBe([]);
    }

    public function test_enum_registry_manifest_generation(): void
    {
        $registry = new EnumRegistry([TripStatus::class, UserRole::class]);
        $manifest = $registry->manifest();

        expect($manifest)
            ->toHaveKeys(['TripStatus', 'UserRole'])
            ->and($manifest['TripStatus'])->toHaveKey('name', 'TripStatus')
            ->and($manifest['UserRole'])->toHaveKey('name', 'UserRole');
    }

    public function test_enum_registry_filters_invalid_classes(): void
    {
        $registry = new EnumRegistry([TripStatus::class, 'NonExistentClass', stdClass::class]);
        $manifest = $registry->manifest();

        expect($manifest)->toHaveKeys(['TripStatus']);
    }

    public function test_translated_label_with_multiple_locales(): void
    {
        // Create translation files for testing
        $this->app['path.lang'] = __DIR__.'/lang';
        foreach (['en', 'fr', 'es'] as $locale) {
            if (! is_dir(__DIR__."/lang/{$locale}")) {
                mkdir(__DIR__."/lang/{$locale}", 0755, true);
            }
        }

        file_put_contents(__DIR__.'/lang/en/orders.php', "<?php return ['pending' => 'Pending Order'];");
        file_put_contents(__DIR__.'/lang/fr/orders.php', "<?php return ['pending' => 'Commande en attente'];");
        file_put_contents(__DIR__.'/lang/es/orders.php', "<?php return ['pending' => 'Pedido pendiente'];");

        config(['enumshare.locales' => ['en', 'fr', 'es']]);

        $result = OrderStatus::forFrontend();
        $pendingEntry = collect($result['entries'])->firstWhere('key', 'Pending');

        expect($pendingEntry['label'])
            ->toBeArray()
            ->toHaveKeys(['en', 'fr', 'es'])
            ->and($pendingEntry['label']['en'])->toBe('Pending Order')
            ->and($pendingEntry['label']['fr'])->toBe('Commande en attente')
            ->and($pendingEntry['label']['es'])->toBe('Pedido pendiente');

        // Cleanup
        foreach (['en', 'fr', 'es'] as $locale) {
            @unlink(__DIR__."/lang/{$locale}/orders.php");
            @rmdir(__DIR__."/lang/{$locale}");
        }
        @rmdir(__DIR__.'/lang');
    }

    public function test_translated_label_with_parameters(): void
    {
        // Create translation files for testing
        $this->app['path.lang'] = __DIR__.'/lang';
        if (! is_dir(__DIR__.'/lang/en')) {
            mkdir(__DIR__.'/lang/en', 0755, true);
        }
        file_put_contents(__DIR__.'/lang/en/orders.php', "<?php return ['confirmed' => 'Confirmed :status Order'];");

        config(['enumshare.locales' => ['en']]);

        $result = OrderStatus::forFrontend();
        $confirmedEntry = collect($result['entries'])->firstWhere('key', 'Confirmed');

        expect($confirmedEntry['label'])
            ->toBeArray()
            ->and($confirmedEntry['label']['en'])->toBe('Confirmed active Order');

        // Cleanup
        @unlink(__DIR__.'/lang/en/orders.php');
        @rmdir(__DIR__.'/lang/en');
        @rmdir(__DIR__.'/lang');
    }

    public function test_translated_label_fallback_to_single_locale_when_no_locales_configured(): void
    {
        // Create translation files for testing
        $this->app['path.lang'] = __DIR__.'/lang';
        if (! is_dir(__DIR__.'/lang/en')) {
            mkdir(__DIR__.'/lang/en', 0755, true);
        }
        file_put_contents(__DIR__.'/lang/en/orders.php', "<?php return ['pending' => 'Pending Order'];");

        config(['app.locale' => 'en']);
        config(['enumshare.locales' => []]);

        $result = OrderStatus::forFrontend();
        $pendingEntry = collect($result['entries'])->firstWhere('key', 'Pending');

        expect($pendingEntry['label'])->toBe('Pending Order');

        // Cleanup
        @unlink(__DIR__.'/lang/en/orders.php');
        @rmdir(__DIR__.'/lang/en');
        @rmdir(__DIR__.'/lang');
    }

    public function test_mixed_label_types_in_same_enum(): void
    {
        // Create translation files for testing
        $this->app['path.lang'] = __DIR__.'/lang';
        if (! is_dir(__DIR__.'/lang/en')) {
            mkdir(__DIR__.'/lang/en', 0755, true);
        }
        file_put_contents(__DIR__.'/lang/en/orders.php', "<?php return ['pending' => 'Pending Order'];");

        config(['enumshare.locales' => ['en']]);

        $result = OrderStatus::forFrontend();
        $pendingEntry = collect($result['entries'])->firstWhere('key', 'Pending');
        $cancelledEntry = collect($result['entries'])->firstWhere('key', 'Cancelled');

        expect($pendingEntry['label'])
            ->toBeArray()
            ->and($pendingEntry['label']['en'])->toBe('Pending Order')
            ->and($cancelledEntry['label'])->toBe('Cancelled');

        // Cleanup
        @unlink(__DIR__.'/lang/en/orders.php');
        @rmdir(__DIR__.'/lang/en');
        @rmdir(__DIR__.'/lang');
    }

    public function test_options_use_string_labels_for_translated_labels(): void
    {
        // Create translation files for testing
        $this->app['path.lang'] = __DIR__.'/lang';
        if (! is_dir(__DIR__.'/lang/en')) {
            mkdir(__DIR__.'/lang/en', 0755, true);
        }
        file_put_contents(__DIR__.'/lang/en/orders.php', "<?php return ['pending' => 'Pending Order'];");

        config(['app.locale' => 'en']);
        config(['enumshare.locales' => ['en']]);

        $result = OrderStatus::forFrontend();
        $pendingOption = collect($result['options'])->firstWhere('value', 'pending');

        expect($pendingOption['label'])->toBe('Pending Order');

        // Cleanup
        @unlink(__DIR__.'/lang/en/orders.php');
        @rmdir(__DIR__.'/lang/en');
        @rmdir(__DIR__.'/lang');
    }

    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('enumshare.enums', [
            TripStatus::class,
            UserRole::class,
        ]);
    }
}
