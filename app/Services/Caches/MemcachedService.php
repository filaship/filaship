<?php

declare(strict_types = 1);

namespace Filaship\Services\Caches;

use Filaship\DockerCompose\Service;
use Filaship\Services\BaseService;

class MemcachedService extends BaseService
{
    public function getName(): string
    {
        return 'memcached';
    }

    public function getDescription(): string
    {
        return 'Memcached High-Performance Memory Cache';
    }

    public function getCategory(): string
    {
        return 'cache';
    }

    public function createService(): Service
    {
        return $this->createBaseService(
            name: $this->getName(),
            image: 'memcached:alpine',
            ports: $this->getDefaultPorts()
        );
    }

    public function getDefaultPorts(): array
    {
        return ['11211:11211'];
    }

    public function getConnectionString(): string
    {
        return 'memcached://memcached:11211';
    }
}
