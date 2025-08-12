<?php

use Illuminate\Support\Facades\View;
use Olivermbs\LaravelEnumshare\LaravelEnumshareServiceProvider;

it('generates typescript from blade template', function () {
    // Register the service provider to load views
    $this->app->register(LaravelEnumshareServiceProvider::class);
    
    $enumData = [
        'name' => 'TestStatus',
        'fqcn' => 'Tests\\TestStatus',
        'backingType' => 'string',
        'entries' => [
            [
                'key' => 'Active',
                'value' => 'active',
                'label' => 'Active Status',
                'meta' => ['color' => 'green', 'icon' => 'check'],
            ],
            [
                'key' => 'Pending',
                'value' => 'pending',
                'label' => 'Pending Status', 
                'meta' => ['color' => 'yellow', 'icon' => 'clock'],
            ]
        ],
        'options' => [
            ['value' => 'active', 'label' => 'Active Status'],
            ['value' => 'pending', 'label' => 'Pending Status']
        ]
    ];

    $output = View::make('enumshare::enum', $enumData)->render();
    
    // Check basic structure
    expect($output)->toContain('export type TestStatus');
    expect($output)->toContain('export const TestStatus');
    expect($output)->toContain("key: 'Active'");
    expect($output)->toContain("value: 'active'");
    expect($output)->toContain('from(value:');
    expect($output)->toContain('fromKey(key:');
    expect($output)->not->toContain('EnumRuntime');
    
    // Verify it's valid TypeScript structure
    expect($output)->toContain('} as const;');
    
    // Check JSDoc comments are present
    expect($output)->toContain('/**');
    expect($output)->toContain(' * TestStatus enum generated from');
    
    // Check new utility methods
    expect($output)->toContain('isValid(value: unknown): boolean');
    expect($output)->toContain('random(): TestStatusEntry');
    expect($output)->toContain('count: 2');
});