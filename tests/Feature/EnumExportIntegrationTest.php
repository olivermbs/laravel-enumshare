<?php

use Illuminate\Support\Facades\File;
use Olivermbs\LaravelEnumshare\LaravelEnumshareServiceProvider;

require_once __DIR__.'/../Fixtures/TestEnum.php';
require_once __DIR__.'/../Fixtures/MultilingualTestEnum.php';

beforeEach(function () {
    $this->app->register(LaravelEnumshareServiceProvider::class);

    // Set up a test directory for generated files
    $this->testOutputDir = sys_get_temp_dir().'/enum-test-'.uniqid();
    File::makeDirectory($this->testOutputDir);
});

afterEach(function () {
    // Clean up test files
    if (File::exists($this->testOutputDir)) {
        File::deleteDirectory($this->testOutputDir);
    }
});

it('generates valid TypeScript files for enums', function () {
    // Configure test enum
    config(['enumshare.enums' => ['Olivermbs\\LaravelEnumshare\\Tests\\Fixtures\\TestEnum']]);
    config(['enumshare.path' => $this->testOutputDir]);

    // Run export command
    $this->artisan('enums:export')
        ->assertExitCode(0);

    // Verify file was created (uses short name, not full namespace)
    $filePath = $this->testOutputDir.'/TestEnum.ts';
    expect(File::exists($filePath))->toBeTrue();

    // Verify content structure
    $content = File::get($filePath);
    expect($content)->toContain('export type TestEnumMeta');
    expect($content)->toContain('export type TestEnumEntry');
    expect($content)->toContain('export type TestEnumOption');
    expect($content)->toContain('export const TestEnum');
    expect($content)->toContain('// Precomputed lookup maps for O(1) access');
    expect($content)->toContain('// Lookup methods');
    expect($content)->toContain('// Type guard methods');
});

it('handles export command with invalid enum gracefully', function () {
    // Configure with invalid enum class
    config(['enumshare.enums' => ['NonExistentEnum']]);
    config(['enumshare.path' => $this->testOutputDir]);

    // Run export command - should succeed but with warnings
    $this->artisan('enums:export')
        ->assertExitCode(0)
        ->expectsOutput('Validating enums...')
        ->expectsOutput('Some configured enums have validation errors:');

    // No files should be created
    $files = File::files($this->testOutputDir);
    expect($files)->toBeEmpty();
});

it('handles directory creation errors gracefully', function () {
    // Try to export to an invalid directory (read-only parent)
    $invalidDir = '/root/enum-test-'.uniqid();
    config(['enumshare.enums' => ['Olivermbs\\LaravelEnumshare\\Tests\\Fixtures\\TestEnum']]);
    config(['enumshare.path' => $invalidDir]);

    $this->artisan('enums:export')
        ->assertExitCode(1)
        ->expectsOutputToContain('Export failed: Failed to create directory:');
});

it('exports enums with custom methods correctly', function () {
    // Test with enum that has custom methods
    config(['enumshare.enums' => ['Olivermbs\\LaravelEnumshare\\Tests\\Fixtures\\TestEnum']]);
    config(['enumshare.path' => $this->testOutputDir]);

    $this->artisan('enums:export')->assertExitCode(0);

    $content = File::get($this->testOutputDir.'/TestEnum.ts');

    // Should contain generated type definition with proper structure
    expect($content)->toContain('TestEnumEntry');
    expect($content)->toContain('readonly key:');
    expect($content)->toContain('readonly value:');
    expect($content)->toContain('readonly label:');
    expect($content)->toContain('readonly meta:');
});

it('exports multilingual enums correctly', function () {
    // Configure for multilingual support
    config(['enumshare.enums' => ['Olivermbs\\LaravelEnumshare\\Tests\\Fixtures\\MultilingualTestEnum']]);
    config(['enumshare.path' => $this->testOutputDir]);
    config(['enumshare.locales' => ['en', 'es']]);

    $this->artisan('enums:export')->assertExitCode(0);

    $content = File::get($this->testOutputDir.'/MultilingualTestEnum.ts');

    // Should handle multilingual labels if configured
    expect($content)->toContain('labels(');
    expect($content)->toContain('locale?:');
});

it('validates enum configuration before export', function () {
    // Mix of valid and invalid enums
    config(['enumshare.enums' => ['Olivermbs\\LaravelEnumshare\\Tests\\Fixtures\\TestEnum', 'NonExistentEnum', 'InvalidClass']]);
    config(['enumshare.path' => $this->testOutputDir]);

    $result = $this->artisan('enums:export');

    $result->expectsOutput('Validating enums...');
    $result->expectsOutput('Some configured enums have validation errors:');
    $result->expectsOutput('1/3 configured enums are valid');
    $result->expectsOutputToContain('Exported 1 enum(s) to:'); // Should still export valid enums
    $result->assertExitCode(0);
});

it('provides verbose error output when requested', function () {
    // Create scenario that will cause an exception during generation
    config(['enumshare.enums' => ['Olivermbs\\LaravelEnumshare\\Tests\\Fixtures\\TestEnum']]);
    config(['enumshare.path' => '/root/invalid/path/that/cannot/be/created']);

    $this->artisan('enums:export', ['-v' => true])
        ->assertExitCode(1)
        ->expectsOutputToContain('Export failed');
});

it('handles empty enum configuration gracefully', function () {
    config(['enumshare.enums' => []]);
    config(['enumshare.auto_discovery' => false]);
    config(['enumshare.path' => $this->testOutputDir]);

    $this->artisan('enums:export')
        ->assertExitCode(0)
        ->expectsOutput('No enums configured and auto-discovery is disabled.')
        ->expectsOutput('No enums found to export.');
});

it('generates files with proper TypeScript syntax', function () {
    config(['enumshare.enums' => ['Olivermbs\\LaravelEnumshare\\Tests\\Fixtures\\TestEnum']]);
    config(['enumshare.path' => $this->testOutputDir]);

    $this->artisan('enums:export')->assertExitCode(0);

    $content = File::get($this->testOutputDir.'/TestEnum.ts');

    // Basic TypeScript syntax checks
    expect($content)->toMatch('/export\s+type\s+\w+/'); // Has type exports
    expect($content)->toMatch('/export\s+const\s+\w+/'); // Has const exports
    expect($content)->toContain('readonly '); // Uses readonly properties
    expect($content)->toContain('as const'); // Uses const assertions
    expect($content)->not->toContain(': undefined'); // No undefined values
    expect($content)->not->toContain('= undefined'); // No undefined assignments
    expect($content)->not->toContain('<?php'); // No PHP leakage
});
