<?php

use Illuminate\Support\Facades\File;
use Olivermbs\LaravelEnumshare\Tests\TestCase;

class EnumCommandsTest extends TestCase
{
    protected string $testEnumsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testEnumsPath = base_path('test_enums');

        if (File::isDirectory($this->testEnumsPath)) {
            File::deleteDirectory($this->testEnumsPath);
        }

        File::makeDirectory($this->testEnumsPath, 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->testEnumsPath)) {
            File::deleteDirectory($this->testEnumsPath);
        }

        parent::tearDown();
    }

    public function test_enums_discover_command_works(): void
    {
        $this->createTestEnumFile();

        $this->artisan('enums:discover')
            ->expectsOutput('Discovering enums...')
            ->expectsOutputToContain('Found 1 enum(s):')
            ->expectsOutputToContain('App\\Enums\\CommandTestEnum')
            ->assertSuccessful();
    }

    public function test_enums_discover_command_fails_when_disabled(): void
    {
        config(['enumshare.autodiscovery.enabled' => false]);

        $this->artisan('enums:discover')
            ->expectsOutput('Enum autodiscovery is not enabled. Enable it in config/enumshare.php')
            ->assertFailed();
    }

    public function test_enums_discover_command_shows_warning_when_no_enums_found(): void
    {
        $this->artisan('enums:discover')
            ->expectsOutput('Discovering enums...')
            ->expectsOutput('No enums found that implement the FrontendEnum contract.')
            ->assertSuccessful();
    }

    public function test_enums_export_works_with_autodiscovery(): void
    {
        $this->createTestEnumFile();

        $tempDir = sys_get_temp_dir().'/enumshare-export-test-'.time();

        $this->artisan('enums:export', [
            '--path' => $tempDir,
        ])
            ->expectsOutput('Generating enum manifest...')
            ->expectsOutputToContain('Exported 1 enum(s) to:')
            ->assertSuccessful();

        // Check individual enum file is created
        expect(File::exists($tempDir.'/CommandTestEnum.ts'))->toBeTrue();

        $enumContent = File::get($tempDir.'/CommandTestEnum.ts');
        expect($enumContent)
            ->toContain('export const CommandTestEnum')
            ->toContain('buildEnum');

        // Clean up
        File::deleteDirectory($tempDir);
    }

    protected function createTestEnumFile(): void
    {
        $directory = $this->testEnumsPath.'/App/Enums';
        File::makeDirectory($directory, 0755, true);

        $content = "<?php

namespace App\\Enums;

use Olivermbs\\LaravelEnumshare\\Concerns\\SharesWithFrontend;
use Olivermbs\\LaravelEnumshare\\Contracts\\FrontendEnum;

enum CommandTestEnum: string implements FrontendEnum
{
    use SharesWithFrontend;
    
    case Active = 'active';
    case Inactive = 'inactive';
}";

        File::put($directory.'/CommandTestEnum.php', $content);
        require_once $directory.'/CommandTestEnum.php';
    }

    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('enumshare.autodiscovery', [
            'enabled' => true,
            'paths' => ['test_enums'],
            'namespaces' => ['App\\Enums\\*'],
            'cache' => [
                'enabled' => false,
                'key' => 'test.discovered_enums',
                'ttl' => 3600,
            ],
        ]);
    }
}
