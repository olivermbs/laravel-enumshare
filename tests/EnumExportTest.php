<?php

use Olivermbs\LaravelEnumshare\Attributes\Label;
use Olivermbs\LaravelEnumshare\Attributes\Meta;
use Olivermbs\LaravelEnumshare\Concerns\SharesWithFrontend;
use Olivermbs\LaravelEnumshare\Contracts\FrontendEnum;
use Olivermbs\LaravelEnumshare\Support\EnumRegistry;
use Olivermbs\LaravelEnumshare\Tests\TestCase;

enum TripStatus: string implements FrontendEnum
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

enum UserRole implements FrontendEnum
{
    use SharesWithFrontend;

    case Admin;
    case User;
    case Guest;
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

    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('enumshare.enums', [
            TripStatus::class,
            UserRole::class,
        ]);
    }
}
