<?php

declare(strict_types = 1);

namespace Filaship\Services\Search;

use Filaship\DockerCompose\Service;
use Filaship\DockerCompose\Volume;
use Filaship\Services\BaseService;

class ElasticsearchService extends BaseService
{
    public function getName(): string
    {
        return 'elasticsearch';
    }

    public function getDescription(): string
    {
        return 'Elasticsearch 8.11 - Search and Analytics Engine';
    }

    public function getCategory(): string
    {
        return 'search';
    }

    public function createService(): Service
    {
        $service              = new Service(name: $this->getName());
        $service->image       = 'elasticsearch:8.11.1';
        $service->environment = [
            'discovery.type=single-node',
            'ES_JAVA_OPTS=-Xms512m -Xmx512m',
            'xpack.security.enabled=false',
            'ELASTIC_PASSWORD=password',
        ];
        $service->ports    = ['9200:9200', '9300:9300'];
        $service->networks = ['backend'];
        $service->volumes  = ['elasticsearch_data:/usr/share/elasticsearch/data'];

        return $service;
    }

    public function getRequiredVolumes(): array
    {
        return [
            new Volume(
                name: 'elasticsearch_data',
                driver: 'local'
            ),
        ];
    }
}
