<?php

declare(strict_types = 1);

namespace Filaship\Services\Mail;

use Filaship\DockerCompose\Service;
use Filaship\Enums\ServiceCategories;
use Filaship\Services\BaseService;

class MailHogService extends BaseService
{
    public function getName(): string
    {
        return 'mailhog';
    }

    public function getDescription(): string
    {
        return 'MailHog Email Testing Tool';
    }

    public function getCategory(): ServiceCategories
    {
        return ServiceCategories::MAIL;
    }

    public function createService(): Service
    {
        return $this->createBaseService(
            name: $this->getName(),
            image: 'mailhog/mailhog:latest',
            ports: $this->getDefaultPorts()
        );
    }

    public function getDefaultPorts(): array
    {
        return ['1025:1025', '8025:8025'];
    }
}
