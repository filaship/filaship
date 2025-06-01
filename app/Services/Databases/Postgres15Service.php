<?php

declare(strict_types = 1);

namespace Filaship\Services\Databases;

use Filaship\DockerCompose\Service;
use Filaship\Enums\ServiceCategories;
use Filaship\Services\BaseService;

class Postgres15Service extends BaseService
{
    public function getName(): string
    {
        return 'postgres';
    }

    public function getDescription(): string
    {
        return 'PostgreSQL 15 Database Server';
    }

    public function getCategory(): ServiceCategories
    {
        return ServiceCategories::DATABASE;
    }

    public function createService(): Service
    {
        return $this->createBaseService(
            name: $this->getName(),
            image: 'postgres:15',
            environment: $this->getDefaultEnvironment(),
            ports: $this->getDefaultPorts(),
            volumes: ['postgres_data:/var/lib/postgresql/data']
        );
    }

    public function getDefaultEnvironment(): array
    {
        return [
            'POSTGRES_DB=app',
            'POSTGRES_USER=user',
            'POSTGRES_PASSWORD=password',
        ];
    }

    public function getDefaultPorts(): array
    {
        return ['5432:5432'];
    }

    public function getRequiredVolumes(): array
    {
        return [
            $this->createVolume('postgres_data'),
        ];
    }

    public function getConnectionString(): string
    {
        return 'postgresql://user:password@postgres:5432/app';
    }
}
