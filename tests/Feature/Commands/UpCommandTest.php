<?php

declare(strict_types = 1);

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    Process::preventStrayProcesses();
});

test('runs docker compose up successfully with default parameters', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => Process::result(
            output: 'Container started successfully',
        ),
    ]);

    $this->artisan('up')
        ->assertSuccessful();

    Process::assertRan('docker compose -f docker-compose.yml up ');
});

test('runs docker compose up in detached mode when -d flag is provided', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up -d' => Process::result(
            output: 'Container started in detached mode',
        ),
    ]);

    $this->artisan('up', ['--detached' => true])
        ->assertSuccessful();

    Process::assertRan('docker compose -f docker-compose.yml up -d');
});

test('runs docker compose up with custom compose file when -f flag is provided', function () {
    Process::fake([
        'docker compose -f custom-compose.yml up ' => Process::result(
            output: 'Container started with custom file',
        ),
    ]);

    $this->artisan('up', ['--file' => 'custom-compose.yml'])
        ->assertSuccessful();

    Process::assertRan('docker compose -f custom-compose.yml up ');
});

test('runs docker compose up with both detached mode and custom file', function () {
    Process::fake([
        'docker compose -f production.yml up -d' => Process::result(
            output: 'Production containers started in detached mode',
        ),
    ]);

    $this->artisan('up', [
        '--detached' => true,
        '--file'     => 'production.yml',
    ])
        ->assertSuccessful();

    Process::assertRan('docker compose -f production.yml up -d');
});

test('handles docker compose up failure gracefully', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => Process::result(
            output: '',
            errorOutput: 'Error: Service web failed to start',
            exitCode: 1
        ),
    ]);

    $this->artisan('up')
        ->assertFailed();

    Process::assertRan('docker compose -f docker-compose.yml up ');
});

test('handles docker compose up with network errors', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => Process::result(
            output: '',
            errorOutput: 'ERROR: Network filaship_default not found',
            exitCode: 1
        ),
    ]);

    $this->artisan('up')
        ->assertFailed();

    Process::assertRan('docker compose -f docker-compose.yml up ');
});

test('handles docker compose up with missing image errors', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => Process::result(
            output: '',
            errorOutput: 'ERROR: pull access denied for nonexistent/image',
            exitCode: 125
        ),
    ]);

    $this->artisan('up')
        ->assertExitCode(1);

    Process::assertRan('docker compose -f docker-compose.yml up ');
});

test('validates process command structure with closure assertion', function () {
    Process::fake([
        '*' => Process::result(output: 'Success'),
    ]);

    $this->artisan('up', ['--file' => 'test.yml', '--detached' => true])
        ->assertSuccessful();

    Process::assertRan(function (PendingProcess $process, ProcessResult $result) {
        return str_contains($process->command, 'docker compose') &&
               str_contains($process->command, '-f test.yml') &&
               str_contains($process->command, 'up -d');
    });
});

test('shows spinning message while starting containers', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => Process::describe()
            ->output('Creating network...')
            ->output('Creating volume...')
            ->output('Creating containers...')
            ->output('Starting containers...')
            ->exitCode(0)
            ->iterations(3),
    ]);

    $this->artisan('up')
        ->assertSuccessful();
});

test('handles docker compose up with service dependency errors', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => Process::result(
            output: '',
            errorOutput: 'ERROR: Service \'web\' depends on service \'db\' which is undefined',
            exitCode: 1
        ),
    ]);

    $this->artisan('up')
        ->assertFailed();
});

test('handles docker compose up with port binding conflicts', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => Process::result(
            output: '',
            errorOutput: 'ERROR: for web  Cannot start service web: driver failed programming external connectivity on endpoint: bind for 0.0.0.0:80 failed: port is already allocated',
            exitCode: 1
        ),
    ]);

    $this->artisan('up')
        ->assertFailed();
});

test('runs command with short flags (-d and -f)', function () {
    Process::fake([
        'docker compose -f staging.yml up -d' => Process::result(
            output: 'Staging containers started',
        ),
    ]);

    $this->artisan('up', ['-d' => true, '-f' => 'staging.yml'])
        ->assertSuccessful();

    Process::assertRan('docker compose -f staging.yml up -d');
});

test('ensures no stray processes are executed during tests', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => 'Success',
    ]);

    $this->artisan('up')->assertSuccessful();

    // This should not execute any real processes
    Process::assertRanTimes('docker compose -f docker-compose.yml up ', 1);
});

test('handles complex docker compose output with multiple services', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => Process::result(
            output: "Creating network \"filaship_default\" with the default driver\n" .
                   "Creating volume \"filaship_db_data\" with default driver\n" .
                   "Creating filaship_db_1 ... done\n" .
                   "Creating filaship_web_1 ... done\n" .
                   "Creating filaship_nginx_1 ... done",
        ),
    ]);

    $this->artisan('up')
        ->assertSuccessful();

    Process::assertRan(function (PendingProcess $process, ProcessResult $result) {
        return $process->command === 'docker compose -f docker-compose.yml up ' &&
               $result->successful();
    });
});

test('handles docker daemon not running error', function () {
    Process::fake([
        'docker compose -f docker-compose.yml up ' => Process::result(
            output: '',
            errorOutput: 'Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?',
            exitCode: 1
        ),
    ]);

    $this->artisan('up')
        ->assertFailed();
});

test('validates command signature and options', function () {
    // Test that the command accepts the expected options
    $command = $this->app->make(Filaship\Commands\UpCommand::class);

    // Test command signature contains expected options
    $definition = $command->getDefinition();
    expect($definition->hasOption('detached'))->toBeTrue()
        ->and($definition->hasOption('file'))->toBeTrue()
        ->and($definition->getOption('detached')->getShortcut())->toBe('d')
        ->and($definition->getOption('file')->getShortcut())->toBe('f');
});
