<?php

declare(strict_types = 1);

use Filaship\Contracts\ServiceTemplateInterface;
use Filaship\Services\ServiceRegistry;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->registry = new ServiceRegistry();
});

test('initializes with predefined categories', function () {
    $categories = $this->registry->getCategories();

    expect($categories)->toBeArray()
        ->and($categories)->toHaveKey('database')
        ->and($categories)->toHaveKey('cache')
        ->and($categories)->toHaveKey('monitoring')
        ->and($categories)->toHaveKey('mail')
        ->and($categories)->toHaveKey('storage')
        ->and($categories)->toHaveKey('search')
        ->and($categories)->toHaveKey('tool');
});

test('returns available categories', function () {
    $categories = $this->registry->getAvailableCategories();

    expect($categories)->toBeArray()
        ->and($categories)
        ->toContain('database', 'cache', 'mail', 'tool');
});

test('can retrieve all services', function () {
    $services = $this->registry->getAllServices();

    expect($services)->toBeInstanceOf(Collection::class)
        ->and($services->count())->toBeGreaterThan(0);
});

test('can filter services by category', function () {
    $databaseServices = $this->registry->getServicesByCategory('database');

    expect($databaseServices)->toBeInstanceOf(Collection::class)
        ->and($databaseServices->count())->toBeGreaterThan(0);

    $databaseServices->each(function ($service) {
        expect($service)->toBeInstanceOf(ServiceTemplateInterface::class)
            ->and($service->getCategory())->toBe('database');
    });
});

test('can retrieve specific service by name', function () {
    $mysql = $this->registry->getService('mysql');

    expect($mysql)->toBeInstanceOf(ServiceTemplateInterface::class)
        ->and($mysql->getName())->toBe('mysql')
        ->and($mysql->getCategory())->toBe('database');
});

test('returns null for non-existent service', function () {
    $nonExistent = $this->registry->getService('non-existent');

    expect($nonExistent)->toBeNull();
});

test('has database services', function () {
    $databaseServices = $this->registry->getDatabaseServices();

    expect($databaseServices)->toBeInstanceOf(Collection::class)
        ->and($databaseServices->count())->toBe(4); // MySQL, PostgreSQL, MongoDB, MariaDB
});

test('has cache services', function () {
    $cacheServices = $this->registry->getCacheServices();

    expect($cacheServices)->toBeInstanceOf(Collection::class)
        ->and($cacheServices->count())->toBe(2); // Redis, Memcached
});

test('has mail services', function () {
    $mailServices = $this->registry->getMailServices();

    expect($mailServices)->toBeInstanceOf(Collection::class)
        ->and($mailServices->count())->toBe(1); // MailHog
});

test('has monitoring services', function () {
    $monitoringServices = $this->registry->getMonitoringServices();

    expect($monitoringServices)->toBeInstanceOf(Collection::class)
        ->and($monitoringServices->count())->toBe(1); // Grafana
});

test('has storage services', function () {
    $storageServices = $this->registry->getStorageServices();

    expect($storageServices)->toBeInstanceOf(Collection::class)
        ->and($storageServices->count())->toBe(1); // MinIO
});

test('has search services', function () {
    $searchServices = $this->registry->getSearchServices();

    expect($searchServices)->toBeInstanceOf(Collection::class)
        ->and($searchServices->count())->toBe(1); // Elasticsearch
});

test('has tool services', function () {
    $toolServices = $this->registry->getToolServices();

    expect($toolServices)->toBeInstanceOf(Collection::class)
        ->and($toolServices->count())->toBe(1); // Adminer
});
