<?php

declare(strict_types = 1);

namespace Filaship\Services\Caches;

use Filaship\DockerCompose\Service;
use Filaship\Enums\ServiceCategories;
use Filaship\Services\BaseService;

class Redis7Service extends BaseService
{
    public function getName(): string
    {
        return 'redis';
    }

    public function getDescription(): string
    {
        return 'Redis 7 In-Memory Cache & Message Broker';
    }

    public function getCategory(): ServiceCategories
    {
        return ServiceCategories::CACHE;
    }

    public function createService(): Service
    {
        $service = $this->createBaseService(
            name: $this->getName(),
            image: 'redis:7-alpine',
            ports: $this->getDefaultPorts(),
            volumes: ['redis_data:/data']
        );

        $service->command = 'redis-server --appendonly yes';

        return $service;
    }

    public function getDefaultPorts(): array
    {
        return ['6379:6379'];
    }

    public function getRequiredVolumes(): array
    {
        return [
            $this->createVolume('redis_data'),
        ];
    }

    public function getConnectionString(): string
    {
        return 'redis://redis:6379';
    }
}
