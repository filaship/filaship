<?php

declare(strict_types = 1);

namespace Filaship\Services;

use Filaship\Contracts\ServiceTemplateInterface;
use Filaship\Services\Caches\MemcachedService;
use Filaship\Services\Caches\Redis7Service;
use Filaship\Services\Databases\MariaDb11Service;
use Filaship\Services\Databases\MongoDb7Service;
use Filaship\Services\Databases\Mysql8Service;
use Filaship\Services\Databases\Postgres15Service;
use Filaship\Services\Mail\MailHogService;
use Filaship\Services\Monitoring\GrafanaService;
use Filaship\Services\Search\ElasticsearchService;
use Filaship\Services\Storage\MinioService;
use Filaship\Services\Tools\AdminerService;
use Illuminate\Support\Collection;

class ServiceRegistry
{
    private Collection $services;

    private array $categories;

    public function __construct()
    {
        $this->services = new Collection();
        $this->initializeCategories();
        $this->registerServices();
    }

    private function initializeCategories(): void
    {
        $this->categories = [
            'database'   => 'Databases (MySQL, PostgreSQL, MongoDB, etc.)',
            'cache'      => 'Cache Systems (Redis, Memcached)',
            'monitoring' => 'Monitoring (Grafana)',
            'mail'       => 'Email Testing (MailHog)',
            'storage'    => 'Storage Solutions (MinIO)',
            'search'     => 'Search Engines (Elasticsearch)',
            'tool'       => 'Development Tools (Adminer)',
        ];
    }

    private function registerServices(): void
    {
        // Databases
        $this->register(new Mysql8Service());
        $this->register(new Postgres15Service());
        $this->register(new MongoDb7Service());
        $this->register(new MariaDb11Service());

        // Caches
        $this->register(new Redis7Service());
        $this->register(new MemcachedService());

        // Monitoring
        $this->register(new GrafanaService());

        // Search
        $this->register(new ElasticsearchService());

        // Storage
        $this->register(new MinioService());

        // Tools
        $this->register(new MailHogService());
        $this->register(new AdminerService());
    }

    private function register(ServiceTemplateInterface $service): void
    {
        $this->services->put($service->getName(), $service);
    }

    public function getService(string $name): ?ServiceTemplateInterface
    {
        return $this->services->get($name);
    }

    public function getServicesByCategory(string $category): Collection
    {
        return $this->services->filter(fn ($service) => $service->getCategory() === $category);
    }

    public function getAllServices(): Collection
    {
        return $this->services;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getAvailableCategories(): array
    {
        return array_keys($this->categories);
    }

    public function getDatabaseServices(): Collection
    {
        return $this->getServicesByCategory('database');
    }

    public function getCacheServices(): Collection
    {
        return $this->getServicesByCategory('cache');
    }

    public function getMonitoringServices(): Collection
    {
        return $this->getServicesByCategory('monitoring');
    }

    public function getToolServices(): Collection
    {
        return $this->getServicesByCategory('tool');
    }

    public function getMailServices(): Collection
    {
        return $this->getServicesByCategory('mail');
    }

    public function getStorageServices(): Collection
    {
        return $this->getServicesByCategory('storage');
    }

    public function getSearchServices(): Collection
    {
        return $this->getServicesByCategory('search');
    }
}
