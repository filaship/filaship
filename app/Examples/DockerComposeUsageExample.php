<?php

declare(strict_types = 1);

namespace Filaship\Examples;

use Filaship\DockerCompose\DockerCompose;
use Filaship\DockerCompose\Service;
use Filaship\DockerCompose\Service\BuildConfig;
use Illuminate\Support\Collection;

/**
 * Example class demonstrating how to use the DockerCompose parser
 */
class DockerComposeUsageExample
{
    public function parseAndAnalyze(string $filePath): array
    {
        $dockerCompose = new DockerCompose();
        $parsed        = $dockerCompose->parse($filePath);

        return [
            'summary'           => $this->generateSummary($parsed),
            'services_analysis' => $this->analyzeServices($parsed->services),
            'volumes_analysis'  => $this->analyzeVolumes($parsed->volumes),
            'networks_analysis' => $this->analyzeNetworks($parsed->networks),
        ];
    }

    private function generateSummary(DockerCompose $compose): array
    {
        return [
            'version'                => $compose->version,
            'total_services'         => $compose->services->count(),
            'total_volumes'          => $compose->volumes->count(),
            'total_networks'         => $compose->networks->count(),
            'total_configs'          => $compose->configs->count(),
            'total_secrets'          => $compose->secrets->count(),
            'has_external_resources' => $this->hasExternalResources($compose),
        ];
    }

    private function analyzeServices(Collection $services): array
    {
        $analysis = [
            'with_build'       => [],
            'with_image'       => [],
            'exposed_ports'    => [],
            'with_volumes'     => [],
            'with_healthcheck' => [],
        ];

        $services->each(function (Service $service) use (&$analysis) {
            if ($service->build !== null) {
                $analysis['with_build'][] = $service->name;
            }

            if ($service->image !== null) {
                $analysis['with_image'][] = $service->name;
            }

            if (! empty($service->ports)) {
                $analysis['exposed_ports'][$service->name] = $service->ports;
            }

            if (! empty($service->volumes)) {
                $analysis['with_volumes'][] = $service->name;
            }

            if (! empty($service->healthcheck)) {
                $analysis['with_healthcheck'][] = $service->name;
            }
        });

        return $analysis;
    }

    private function analyzeVolumes(Collection $volumes): array
    {
        $external = [];
        $local    = [];

        $volumes->each(function ($volume) use (&$external, &$local) {
            if ($volume->external) {
                $external[] = $volume->name;
            } else {
                $local[] = $volume->name;
            }
        });

        return [
            'external' => $external,
            'local'    => $local,
        ];
    }

    private function analyzeNetworks(Collection $networks): array
    {
        $external = [];
        $local    = [];

        $networks->each(function ($network) use (&$external, &$local) {
            if ($network->external) {
                $external[] = $network->name;
            } else {
                $local[] = $network->name;
            }
        });

        return [
            'external' => $external,
            'local'    => $local,
        ];
    }

    private function hasExternalResources(DockerCompose $compose): bool
    {
        $hasExternalVolumes  = $compose->volumes->contains(fn ($volume) => $volume->external);
        $hasExternalNetworks = $compose->networks->contains(fn ($network) => $network->external);
        $hasExternalConfigs  = $compose->configs->contains(fn ($config) => $config->external);
        $hasExternalSecrets  = $compose->secrets->contains(fn ($secret) => $secret->external);

        return $hasExternalVolumes || $hasExternalNetworks || $hasExternalConfigs || $hasExternalSecrets;
    }

    public function createSimpleCompose(): DockerCompose
    {
        $services = new Collection([
            'web' => new Service(
                name: 'web',
                image: 'nginx:alpine',
                ports: ['80:80', '443:443'],
                volumes: ['./html:/var/www/html:ro'],
                networks: ['frontend']
            ),
            'app' => new Service(
                name: 'app',
                build: new BuildConfig(
                    context: '.',
                    dockerfile: 'Dockerfile',
                    args: ['PHP_VERSION' => '8.2']
                ),
                volumes: ['./src:/var/www/html/src'],
                networks: ['frontend', 'backend'],
                environment: ['APP_ENV=production']
            ),
        ]);

        return new DockerCompose(
            version: '3.8',
            services: $services
        );
    }

    public function modifyExistingCompose(string $filePath): DockerCompose
    {
        $dockerCompose = new DockerCompose();
        $parsed        = $dockerCompose->parse($filePath);

        // Example: Add a new service
        $newService = new Service(
            name: 'monitoring',
            image: 'prom/prometheus:latest',
            ports: ['9090:9090'],
            volumes: ['./prometheus.yml:/etc/prometheus/prometheus.yml:ro']
        );

        $parsed->services->put('monitoring', $newService);

        return $parsed;
    }
}
