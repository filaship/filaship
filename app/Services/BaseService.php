<?php

declare(strict_types = 1);

namespace Filaship\Services;

use Filaship\Contracts\ServiceTemplateInterface;
use Filaship\DockerCompose\Service;
use Filaship\DockerCompose\Volume;

abstract class BaseService implements ServiceTemplateInterface
{
    /**
     * Create a volume with standard naming
     */
    protected function createVolume(string $volumeName, ?string $driver = 'local', array $driverOpts = []): Volume
    {
        return new Volume(
            name: $volumeName,
            driver: $driver,
            driverOpts: $driverOpts
        );
    }

    /**
     * Create a standard service with common defaults
     */
    protected function createBaseService(
        string $name,
        string $image,
        array $environment = [],
        array $ports = [],
        array $volumes = [],
        array $networks = [],
        ?string $restart = 'unless-stopped'
    ): Service {
        return new Service(
            name: $name,
            image: $image,
            environment: $environment,
            ports: $ports,
            volumes: $volumes,
            networks: $networks,
            restart: $restart
        );
    }

    /**
     * Get default environment variables
     */
    public function getDefaultEnvironment(): array
    {
        return [];
    }

    /**
     * Get default ports mapping
     */
    public function getDefaultPorts(): array
    {
        return [];
    }

    /**
     * Check if this service requires external configuration files
     */
    public function requiresExternalConfig(): bool
    {
        return false;
    }

    /**
     * Get list of external config files needed
     */
    public function getRequiredConfigFiles(): array
    {
        return [];
    }

    /**
     * Get required volumes for this service
     */
    public function getRequiredVolumes(): array
    {
        return [];
    }
}
