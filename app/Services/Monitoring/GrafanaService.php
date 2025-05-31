<?php

declare(strict_types = 1);

namespace Filaship\Services\Monitoring;

use Filaship\DockerCompose\Service;
use Filaship\Services\BaseService;

class GrafanaService extends BaseService
{
    public function getName(): string
    {
        return 'grafana';
    }

    public function getDescription(): string
    {
        return 'Grafana Analytics & Monitoring Dashboard';
    }

    public function getCategory(): string
    {
        return 'monitoring';
    }

    public function createService(): Service
    {
        return $this->createBaseService(
            name: $this->getName(),
            image: 'grafana/grafana:latest',
            environment: $this->getDefaultEnvironment(),
            ports: $this->getDefaultPorts(),
            volumes: ['grafana_data:/var/lib/grafana']
        );
    }

    public function getDefaultEnvironment(): array
    {
        return [
            'GF_SECURITY_ADMIN_PASSWORD=admin',
        ];
    }

    public function getDefaultPorts(): array
    {
        return ['3000:3000'];
    }

    public function getRequiredVolumes(): array
    {
        return [
            $this->createVolume('grafana_data'),
        ];
    }
}
