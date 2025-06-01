<?php

declare(strict_types = 1);

use Filaship\Contracts\ServiceTemplateInterface;
use Filaship\Enums\ServiceCategories;
use Filaship\Services\ServiceRegistry;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->registry = new ServiceRegistry();
});

test('initializes with predefined categories', function () {
    $categories = $this->registry->getCategories();

    expect($categories)->toBeArray()
        ->and($categories)
        ->toContain(ServiceCategories::DATABASE, ServiceCategories::CACHE, ServiceCategories::MONITORING, ServiceCategories::MAIL, ServiceCategories::STORAGE, ServiceCategories::SEARCH, ServiceCategories::TOOL);
});

test('can retrieve all services', function () {
    $services = $this->registry->getAllServices();

    expect($services)->toBeInstanceOf(Collection::class)
        ->and($services->count())->toBeGreaterThan(0);
});

test('can filter services by category', function () {
    $databaseServices = $this->registry->getServicesByCategory(ServiceCategories::DATABASE);

    expect($databaseServices)->toBeInstanceOf(Collection::class)
        ->and($databaseServices->count())->toBeGreaterThan(0);

    $databaseServices->each(function ($service) {
        expect($service)->toBeInstanceOf(ServiceTemplateInterface::class)
            ->and($service->getCategory())->toBe(ServiceCategories::DATABASE);
    });
});

test('can retrieve specific service by name', function () {
    $mysql = $this->registry->getService('mysql');

    expect($mysql)->toBeInstanceOf(ServiceTemplateInterface::class)
        ->and($mysql->getName())->toBe('mysql')
        ->and($mysql->getCategory())->toBe(ServiceCategories::DATABASE);
});

test('returns null for non-existent service', function () {
    $nonExistent = $this->registry->getService('non-existent');

    expect($nonExistent)->toBeNull();
});

test('has database services', function () {
    $databaseServices = $this->registry->getDatabaseServices();

    expect($databaseServices)->toBeInstanceOf(Collection::class)
        ->and($databaseServices->count())->toBe(4);
});

test('has cache services', function () {
    $cacheServices = $this->registry->getCacheServices();

    expect($cacheServices)->toBeInstanceOf(Collection::class)
        ->and($cacheServices->count())->toBe(2);
});

test('has mail services', function () {
    $mailServices = $this->registry->getMailServices();

    expect($mailServices)->toBeInstanceOf(Collection::class)
        ->and($mailServices->count())->toBe(1);
});

test('has monitoring services', function () {
    $monitoringServices = $this->registry->getMonitoringServices();

    expect($monitoringServices)->toBeInstanceOf(Collection::class)
        ->and($monitoringServices->count())->toBe(1);
});

test('has storage services', function () {
    $storageServices = $this->registry->getStorageServices();

    expect($storageServices)->toBeInstanceOf(Collection::class)
        ->and($storageServices->count())->toBe(1);
});

test('has search services', function () {
    $searchServices = $this->registry->getSearchServices();

    expect($searchServices)->toBeInstanceOf(Collection::class)
        ->and($searchServices->count())->toBe(1);
});

test('has tool services', function () {
    $toolServices = $this->registry->getToolServices();

    expect($toolServices)->toBeInstanceOf(Collection::class)
        ->and($toolServices->count())->toBe(1);
});
