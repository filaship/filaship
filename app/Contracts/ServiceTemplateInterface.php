<?php

declare(strict_types = 1);

namespace Filaship\Contracts;

use Filaship\DockerCompose\Service;
use Filaship\DockerCompose\Volume;

interface ServiceTemplateInterface
{
    /**
     * Get the service name
     */
    public function getName(): string;

    /**
     * Get the service description
     */
    public function getDescription(): string;

    /**
     * Create the Docker Compose service
     */
    public function createService(): Service;

    /**
     * Get required volumes for this service
     *
     * @return Volume[]
     */
    public function getRequiredVolumes(): array;

    /**
     * Get default environment variables
     */
    public function getDefaultEnvironment(): array;

    /**
     * Get default ports mapping
     */
    public function getDefaultPorts(): array;

    /**
     * Get service category (database, cache, monitoring, etc.)
     */
    public function getCategory(): string;

    /**
     * Check if this service requires external configuration files
     */
    public function requiresExternalConfig(): bool;

    /**
     * Get list of external config files needed
     */
    public function getRequiredConfigFiles(): array;
}
