<?php

declare(strict_types = 1);

namespace Filaship\Services\Storage;

use Filaship\DockerCompose\Service;
use Filaship\Services\BaseService;

class MinioService extends BaseService
{
    public function getName(): string
    {
        return 'minio';
    }

    public function getDescription(): string
    {
        return 'MinIO S3-Compatible Object Storage';
    }

    public function getCategory(): string
    {
        return 'storage';
    }

    public function createService(): Service
    {
        $service = $this->createBaseService(
            name: $this->getName(),
            image: 'minio/minio:latest',
            environment: $this->getDefaultEnvironment(),
            ports: $this->getDefaultPorts(),
            volumes: ['minio_data:/data']
        );

        $service->command = 'server /data --console-address ":9001"';

        return $service;
    }

    public function getDefaultEnvironment(): array
    {
        return [
            'MINIO_ROOT_USER=minioadmin',
            'MINIO_ROOT_PASSWORD=minioadmin',
        ];
    }

    public function getDefaultPorts(): array
    {
        return ['9000:9000', '9001:9001'];
    }

    public function getRequiredVolumes(): array
    {
        return [
            $this->createVolume('minio_data'),
        ];
    }
}
