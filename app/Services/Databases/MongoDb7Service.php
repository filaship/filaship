<?php

declare(strict_types = 1);

namespace Filaship\Services\Databases;

use Filaship\DockerCompose\Service;
use Filaship\Enums\ServiceCategories;
use Filaship\Services\BaseService;

class MongoDb7Service extends BaseService
{
    public function getName(): string
    {
        return 'mongodb';
    }

    public function getDescription(): string
    {
        return 'MongoDB 7 NoSQL Database';
    }

    public function getCategory(): ServiceCategories
    {
        return ServiceCategories::DATABASE;
    }

    public function createService(): Service
    {
        return $this->createBaseService(
            name: $this->getName(),
            image: 'mongo:7',
            environment: $this->getDefaultEnvironment(),
            ports: $this->getDefaultPorts(),
            volumes: ['mongodb_data:/data/db']
        );
    }

    public function getDefaultEnvironment(): array
    {
        return [
            'MONGO_INITDB_ROOT_USERNAME=admin',
            'MONGO_INITDB_ROOT_PASSWORD=password',
            'MONGO_INITDB_DATABASE=app',
        ];
    }

    public function getDefaultPorts(): array
    {
        return ['27017:27017'];
    }

    public function getRequiredVolumes(): array
    {
        return [
            $this->createVolume('mongodb_data'),
        ];
    }

    public function getConnectionString(): string
    {
        return 'mongodb://admin:password@mongodb:27017/app';
    }
}
