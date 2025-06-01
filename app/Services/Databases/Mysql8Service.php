<?php

declare(strict_types = 1);

namespace Filaship\Services\Databases;

use Filaship\DockerCompose\Service;
use Filaship\Enums\ServiceCategories;
use Filaship\Services\BaseService;

class Mysql8Service extends BaseService
{
    public function getName(): string
    {
        return 'mysql';
    }

    public function getDescription(): string
    {
        return 'MySQL 8.0 Database Server';
    }

    public function getCategory(): ServiceCategories
    {
        return ServiceCategories::DATABASE;
    }

    public function createService(): Service
    {
        return $this->createBaseService(
            name: $this->getName(),
            image: 'mysql:8.0',
            environment: $this->getDefaultEnvironment(),
            ports: $this->getDefaultPorts(),
            volumes: ['mysql_data:/var/lib/mysql']
        );
    }

    public function getDefaultEnvironment(): array
    {
        return [
            'MYSQL_ROOT_PASSWORD=rootpassword',
            'MYSQL_DATABASE=app',
            'MYSQL_USER=user',
            'MYSQL_PASSWORD=password',
        ];
    }

    public function getDefaultPorts(): array
    {
        return ['3306:3306'];
    }

    public function getRequiredVolumes(): array
    {
        return [
            $this->createVolume('mysql_data'),
        ];
    }

    public function getConnectionString(): string
    {
        return 'mysql://user:password@mysql:3306/app';
    }
}
