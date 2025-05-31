<?php

declare(strict_types = 1);

use Filaship\Commands\InitCommand;
use Filaship\Services\ServiceRegistry;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up any existing docker-compose.yml files
    if (File::exists('docker-compose.yml')) {
        File::delete('docker-compose.yml');
    }
});

afterEach(function () {
    // Clean up after tests
    if (File::exists('docker-compose.yml')) {
        File::delete('docker-compose.yml');
    }
});

test('has correct signature and description', function () {
    $command = app(InitCommand::class);

    expect($command->getName())->toBe('init')
        ->and($command->getDescription())->toBe('Initialize a new Laravel Docker Compose project with interactive setup');
});

test('creates docker-compose file when none exists', function () {
    $this->artisan('init')
        ->expectsQuestion('What is your project name?', 'test-project')
        ->expectsQuestion('Which categories of services would you like to add?', [])
        ->assertSuccessful();

    expect(File::exists('docker-compose.yml'))->toBeTrue();

    $content = File::get('docker-compose.yml');
    expect($content)->toContain('version: \'3.8\'')
        ->and($content)->toContain('networks:')
        ->and($content)->toContain('test-project:');
});

test('shows existing file options when docker-compose.yml exists', function () {
    // Create a dummy docker-compose.yml file
    File::put('docker-compose.yml', "version: '3.8'\nservices:\n  existing:\n    image: nginx");

    $this->artisan('init')
        ->expectsQuestion('What would you like to do?', 'cancel')
        ->assertSuccessful();
});

test('can add services to existing docker-compose file', function () {
    // Create a basic docker-compose.yml file
    $existingContent = "version: '3.8'\nservices:\n  existing:\n    image: nginx\nnetworks:\n  test:\n    driver: bridge";
    File::put('docker-compose.yml', $existingContent);

    $this->artisan('init')
        ->expectsQuestion('What would you like to do?', 'add')
        ->expectsQuestion('Which categories of services would you like to add?', [])
        ->assertSuccessful();
});

test('can recreate existing docker-compose file', function () {
    // Create a basic docker-compose.yml file
    File::put('docker-compose.yml', "version: '3.8'\nservices:\n  old:\n    image: old-image");

    $this->artisan('init')
        ->expectsQuestion('What would you like to do?', 'recreate')
        ->expectsQuestion('What is your project name?', 'new-project')
        ->expectsQuestion('Which categories of services would you like to add?', [])
        ->assertSuccessful();

    $content = File::get('docker-compose.yml');
    expect($content)->toContain('new-project:')
        ->and($content)->not->toContain('old:');
});

test('creates network with project name', function () {
    $this->artisan('init')
        ->expectsQuestion('What is your project name?', 'my-laravel-app')
        ->expectsQuestion('Which categories of services would you like to add?', [])
        ->assertSuccessful();

    $content = File::get('docker-compose.yml');
    expect($content)->toContain('my-laravel-app:')
        ->and($content)->toContain('driver: bridge');
});

test('validates project name format', function () {
    $this->artisan('init')
        ->expectsQuestion('What is your project name?', 'x') // Too short
        ->expectsQuestion('What is your project name?', 'Invalid-NAME') // Invalid characters
        ->expectsQuestion('What is your project name?', 'valid-project') // Valid
        ->expectsQuestion('Which categories of services would you like to add?', [])
        ->assertSuccessful();
});

test('can select database service', function () {
    // Mock ServiceRegistry to return database services
    $this->mock(ServiceRegistry::class, function ($mock) {
        $mock->shouldReceive('getCategories')->andReturn(['database' => 'Database Services']);
        $mock->shouldReceive('getServicesByCategory')->with('database')->andReturn([
            (object) ['name' => 'mysql', 'description' => 'MySQL 8.0 Database Server']
        ]);
        $mock->shouldReceive('getService')->with('mysql')->andReturn(
            (object) [
                'name' => 'mysql',
                'description' => 'MySQL 8.0 Database Server',
                'createService' => fn() => (object) ['image' => 'mysql:8.0', 'networks' => []],
                'getRequiredVolumes' => fn() => [(object) ['name' => 'mysql_data']],
                'getCategory' => fn() => 'database'
            ]
        );
    });

    $this->artisan('init')
        ->expectsQuestion('What is your project name?', 'test-project')
        ->expectsQuestion('Which categories of services would you like to add?', ['database'])
        ->expectsQuestion('Which Database service would you like to use?', 'mysql')
        ->assertSuccessful();

    $content = File::get('docker-compose.yml');
    expect($content)->toContain('mysql:');
});

test('can select multiple cache services', function () {
    // Mock ServiceRegistry for cache services
    $this->mock(ServiceRegistry::class, function ($mock) {
        $mock->shouldReceive('getCategories')->andReturn(['cache' => 'Cache Services']);
        $mock->shouldReceive('getServicesByCategory')->with('cache')->andReturn([
            (object) ['name' => 'redis', 'description' => 'Redis Cache'],
            (object) ['name' => 'memcached', 'description' => 'Memcached Cache']
        ]);
        $mock->shouldReceive('getService')->with('redis')->andReturn(
            (object) [
                'name' => 'redis',
                'description' => 'Redis Cache',
                'createService' => fn() => (object) ['image' => 'redis:7', 'networks' => []],
                'getRequiredVolumes' => fn() => [],
                'getCategory' => fn() => 'cache'
            ]
        );
        $mock->shouldReceive('getService')->with('memcached')->andReturn(
            (object) [
                'name' => 'memcached',
                'description' => 'Memcached Cache',
                'createService' => fn() => (object) ['image' => 'memcached', 'networks' => []],
                'getRequiredVolumes' => fn() => [],
                'getCategory' => fn() => 'cache'
            ]
        );
    });

    $this->artisan('init')
        ->expectsQuestion('What is your project name?', 'test-project')
        ->expectsQuestion('Which categories of services would you like to add?', ['cache'])
        ->expectsQuestion('Which Cache services would you like to add?', ['redis', 'memcached'])
        ->assertSuccessful();

    $content = File::get('docker-compose.yml');
    expect($content)->toContain('redis:')
        ->and($content)->toContain('memcached:');
});

test('handles no database selection', function () {
    // Mock ServiceRegistry for database services
    $this->mock(ServiceRegistry::class, function ($mock) {
        $mock->shouldReceive('getCategories')->andReturn(['database' => 'Database Services']);
        $mock->shouldReceive('getServicesByCategory')->with('database')->andReturn([
            (object) ['name' => 'mysql', 'description' => 'MySQL 8.0 Database Server']
        ]);
    });

    $this->artisan('init')
        ->expectsQuestion('What is your project name?', 'test-project')
        ->expectsQuestion('Which categories of services would you like to add?', ['database'])
        ->expectsQuestion('Which Database service would you like to use?', 'none')
        ->assertSuccessful();

    $content = File::get('docker-compose.yml');
    expect($content)->not->toContain('mysql:')
        ->and($content)->not->toContain('postgres:');
});

test('creates volumes for services that require them', function () {
    // Mock ServiceRegistry
    $this->mock(ServiceRegistry::class, function ($mock) {
        $mock->shouldReceive('getCategories')->andReturn(['database' => 'Database Services']);
        $mock->shouldReceive('getServicesByCategory')->with('database')->andReturn([
            (object) ['name' => 'mysql', 'description' => 'MySQL 8.0 Database Server']
        ]);
        $mock->shouldReceive('getService')->with('mysql')->andReturn(
            (object) [
                'name' => 'mysql',
                'description' => 'MySQL 8.0 Database Server',
                'createService' => fn() => (object) ['image' => 'mysql:8.0', 'networks' => []],
                'getRequiredVolumes' => fn() => [(object) ['name' => 'mysql_data']],
                'getCategory' => fn() => 'database'
            ]
        );
    });

    $this->artisan('init')
        ->expectsQuestion('What is your project name?', 'test-project')
        ->expectsQuestion('Which categories of services would you like to add?', ['database'])
        ->expectsQuestion('Which Database service would you like to use?', 'mysql')
        ->assertSuccessful();

    $content = File::get('docker-compose.yml');
    expect($content)->toContain('volumes:')
        ->and($content)->toContain('mysql_data');
});

test('configures services with project network', function () {
    // Mock ServiceRegistry
    $this->mock(ServiceRegistry::class, function ($mock) {
        $mock->shouldReceive('getCategories')->andReturn(['database' => 'Database Services']);
        $mock->shouldReceive('getServicesByCategory')->with('database')->andReturn([
            (object) ['name' => 'mysql', 'description' => 'MySQL 8.0 Database Server']
        ]);
        $mock->shouldReceive('getService')->with('mysql')->andReturn(
            (object) [
                'name' => 'mysql',
                'description' => 'MySQL 8.0 Database Server',
                'createService' => fn() => (object) ['image' => 'mysql:8.0', 'networks' => []],
                'getRequiredVolumes' => fn() => [(object) ['name' => 'mysql_data']],
                'getCategory' => fn() => 'database'
            ]
        );
    });

    $this->artisan('init')
        ->expectsQuestion('What is your project name?', 'my-project')
        ->expectsQuestion('Which categories of services would you like to add?', ['database'])
        ->expectsQuestion('Which Database service would you like to use?', 'mysql')
        ->assertSuccessful();

    $content = File::get('docker-compose.yml');
    expect($content)->toContain('networks:')
        ->and($content)->toContain('my-project');
});
