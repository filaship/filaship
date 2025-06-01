<?php

declare(strict_types = 1);

namespace Filaship\Services\Tools;

use Filaship\DockerCompose\Service;
use Filaship\Enums\ServiceCategories;
use Filaship\Services\BaseService;

class AdminerService extends BaseService
{
    public function getName(): string
    {
        return 'adminer';
    }

    public function getDescription(): string
    {
        return 'Adminer Database Management Tool';
    }

    public function getCategory(): ServiceCategories
    {
        return ServiceCategories::TOOL;
    }

    public function createService(): Service
    {
        return $this->createBaseService(
            name: $this->getName(),
            image: 'adminer:latest',
            ports: $this->getDefaultPorts()
        );
    }

    public function getDefaultPorts(): array
    {
        return ['8080:8080'];
    }
}
