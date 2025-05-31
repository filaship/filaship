<?php

declare(strict_types = 1);

use Filaship\DockerCompose\DockerCompose;
use Filaship\DockerCompose\Service;
use Filaship\DockerCompose\Service\BuildConfig;

it('can parse a complete docker compose file', function () {
    $dockerCompose = new DockerCompose();
    $parsed        = $dockerCompose->parse(__DIR__ . '/../../example-docker-compose.yaml');

    expect($parsed->version)->toBe('3.8')
        ->and($parsed->services)->toHaveCount(4)
        ->and($parsed->volumes)->toHaveCount(4)
        ->and($parsed->networks)->toHaveCount(2)
        ->and($parsed->configs)->toHaveCount(1)
        ->and($parsed->secrets)->toHaveCount(2);
});

it('can parse service with build config object', function () {
    $data = [
        'version'  => '3.8',
        'services' => [
            'app' => [
                'build' => [
                    'context'    => '.',
                    'dockerfile' => 'Dockerfile',
                    'args'       => [
                        'BUILD_ARG' => 'value',
                    ],
                ],
                'ports' => ['8080:80'],
            ],
        ],
    ];

    $dockerCompose = new DockerCompose();
    $parsed        = $dockerCompose->parseFromArray($data);

    $service = $parsed->getService('app');
    expect($service)->toBeInstanceOf(Service::class)
        ->and($service->build)->toBeInstanceOf(BuildConfig::class)
        ->and($service->build->context)->toBe('.')
        ->and($service->build->dockerfile)->toBe('Dockerfile')
        ->and($service->build->args)->toBe(['BUILD_ARG' => 'value']);
});

it('can parse service with build as string', function () {
    $data = [
        'version'  => '3.8',
        'services' => [
            'app' => [
                'build' => './app',
                'ports' => ['8080:80'],
            ],
        ],
    ];

    $dockerCompose = new DockerCompose();
    $parsed        = $dockerCompose->parseFromArray($data);

    $service = $parsed->getService('app');
    expect($service)->toBeInstanceOf(Service::class)
        ->and($service->build)->toBeInstanceOf(BuildConfig::class)
        ->and($service->build->context)->toBe('./app')
        ->and($service->build->dockerfile)->toBeNull()
        ->and($service->build->args)->toBe([]);
});

it('can parse service with command as array', function () {
    $data = [
        'version'  => '3.8',
        'services' => [
            'app' => [
                'image'   => 'php:8.2-fpm',
                'command' => ['php-fpm', '--nodaemonize'],
            ],
        ],
    ];

    $dockerCompose = new DockerCompose();
    $parsed        = $dockerCompose->parseFromArray($data);

    $service = $parsed->getService('app');
    expect($service->command)->toBe(['php-fpm', '--nodaemonize']);
});

it('can parse service with command as string', function () {
    $data = [
        'version'  => '3.8',
        'services' => [
            'app' => [
                'image'   => 'nginx',
                'command' => 'nginx -g "daemon off;"',
            ],
        ],
    ];

    $dockerCompose = new DockerCompose();
    $parsed        = $dockerCompose->parseFromArray($data);

    $service = $parsed->getService('app');
    expect($service->command)->toBe('nginx -g "daemon off;"');
});

it('can handle external volumes and networks', function () {
    $data = [
        'version'  => '3.8',
        'services' => [
            'app' => [
                'image'   => 'nginx',
                'volumes' => ['shared_data:/data'],
            ],
        ],
        'volumes' => [
            'shared_data' => [
                'external' => true,
            ],
        ],
        'networks' => [
            'external_net' => [
                'external' => true,
            ],
        ],
    ];

    $dockerCompose = new DockerCompose();
    $parsed        = $dockerCompose->parseFromArray($data);

    $volume = $parsed->getVolume('shared_data');
    expect($volume->external)->toBeTrue();

    $network = $parsed->getNetwork('external_net');
    expect($network->external)->toBeTrue();
});

it('can convert back to array', function () {
    $dockerCompose = new DockerCompose();
    $parsed        = $dockerCompose->parse(__DIR__ . '/../../example-docker-compose.yaml');

    $array = $parsed->toArray();

    expect($array)->toHaveKey('version')
        ->and($array)->toHaveKey('services')
        ->and($array)->toHaveKey('volumes')
        ->and($array)->toHaveKey('networks')
        ->and($array['version'])->toBe('3.8');
});

it('can convert back to yaml', function () {
    $data = [
        'version'  => '3.8',
        'services' => [
            'web' => [
                'image' => 'nginx',
                'ports' => ['80:80'],
            ],
        ],
    ];

    $dockerCompose = new DockerCompose();
    $parsed        = $dockerCompose->parseFromArray($data);

    $yaml = $parsed->toYaml();

    expect($yaml)->toContain('version: \'3.8\'')
        ->and($yaml)->toContain('services:')
        ->and($yaml)->toContain('web:')
        ->and($yaml)->toContain('image: nginx');
});
