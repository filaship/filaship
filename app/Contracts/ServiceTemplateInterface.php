<?php

declare(strict_types = 1);

namespace Filaship\Contracts;

use Filaship\DockerCompose\Service;
use Filaship\DockerCompose\Volume;
use Filaship\Enums\ServiceCategories;

interface ServiceTemplateInterface
{
    public function getName(): string;

    public function getDescription(): string;

    public function createService(): Service;

    /**
     * Get required volumes for this service
     *
     * @return Volume[]
     */
    public function getRequiredVolumes(): array;

    public function getDefaultEnvironment(): array;

    public function getDefaultPorts(): array;

    public function getCategory(): ServiceCategories;

    public function requiresExternalConfig(): bool;

    public function getRequiredConfigFiles(): array;
}
