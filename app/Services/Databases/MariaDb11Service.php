<?php

declare(strict_types = 1);

namespace Filaship\Services\Databases;

use Filaship\DockerCompose\Service;
use Filaship\Enums\ServiceCategories;
use Filaship\Services\BaseService;

class MariaDb11Service extends BaseService
{
    public function getName(): string
    {
        return 'mariadb';
    }

    public function getDescription(): string
    {
        return 'MariaDB 10.11 Database Server';
    }

    public function getCategory(): ServiceCategories
    {
        return ServiceCategories::DATABASE;
    }

    public function createService(): Service
    {
        return $this->createBaseService(
            name: $this->getName(),
            image: 'mariadb:10.11',
            environment: $this->getDefaultEnvironment(),
            ports: $this->getDefaultPorts(),
            volumes: ['mariadb_data:/var/lib/mysql']
        );
    }

    public function getDefaultEnvironment(): array
    {
        return [
            'MARIADB_ROOT_PASSWORD=rootpassword',
            'MARIADB_DATABASE=app',
            'MARIADB_USER=user',
            'MARIADB_PASSWORD=password',
        ];
    }

    public function getDefaultPorts(): array
    {
        return ['3306:3306'];
    }

    public function getRequiredVolumes(): array
    {
        return [
            $this->createVolume('mariadb_data'),
        ];
    }

    public function getConnectionString(): string
    {
        return 'mysql://user:password@mariadb:3306/app';
    }
}
