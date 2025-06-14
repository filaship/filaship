<?php

declare(strict_types = 1);

namespace Filaship\Commands\DockerCompose;

use Filaship\DockerCompose\DockerCompose;
use Filaship\DockerCompose\Network;
use Filaship\DockerCompose\Service;
use Filaship\DockerCompose\Service\BuildConfig;
use Filaship\DockerCompose\Volume;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

/**
 * Example class demonstrating how to use the DockerCompose parser
 */
final class DockerComposeUsageExampleCommand extends Command
{
    protected $signature = 'docker-compose:example {file? : Path to docker-compose.yaml file}';

    protected $description = 'Demonstrate usage of DockerCompose parser with example commands';

    public function handle(): int
    {
        $filePath = $this->argument('file') ?? 'docker-compose.yaml';

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        try {
            $analysis = $this->parseAndAnalyze($filePath);
            $this->info("✅ File parsed successfully!");
            $this->line("Summary:");
            $this->line(json_encode($analysis['summary'], JSON_PRETTY_PRINT));
            $this->line("Services Analysis:");
            $this->line(json_encode($analysis['services_analysis'], JSON_PRETTY_PRINT));
            $this->line("Volumes Analysis:");
            $this->line(json_encode($analysis['volumes_analysis'], JSON_PRETTY_PRINT));
            $this->line("Networks Analysis:");
            $this->line(json_encode($analysis['networks_analysis'], JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error("Error parsing file: " . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseAndAnalyze(string $filePath): array
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

    /**
     * @return array<string, int|string|bool>
     */
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

    /**
     * @param Collection<string, Service> $services
     * @return array<string, mixed>
     */
    private function analyzeServices(Collection $services): array
    {
        $analysis = [
            'with_build'       => [],
            'with_image'       => [],
            'exposed_ports'    => [],
            'with_volumes'     => [],
            'with_healthcheck' => [],
        ];

        $services->each(function (Service $service) use (&$analysis): void {
            if ($service->build instanceof BuildConfig) {
                $analysis['with_build'][] = $service->name;
            }

            if ($service->image !== null) {
                $analysis['with_image'][] = $service->name;
            }

            if ($service->ports !== []) {
                $analysis['exposed_ports'][$service->name] = $service->ports;
            }

            if ($service->volumes !== []) {
                $analysis['with_volumes'][] = $service->name;
            }

            if ($service->healthcheck !== []) {
                $analysis['with_healthcheck'][] = $service->name;
            }
        });

        return $analysis;
    }

    /**
     * @param Collection<string, Volume> $volumes
     * @return array<string, array<int, string>>
     */
    private function analyzeVolumes(Collection $volumes): array
    {
        $external = [];
        $local    = [];

        $volumes->each(function ($volume) use (&$external, &$local): void {
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

    /**
     * @param Collection<string, Network> $networks
     * @return array<string, array<int, string>>
     */
    private function analyzeNetworks(Collection $networks): array
    {
        $external = [];
        $local    = [];

        $networks->each(function ($network) use (&$external, &$local): void {
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
        $hasExternalVolumes  = $compose->volumes->contains(fn ($volume): mixed => $volume->external);
        $hasExternalNetworks = $compose->networks->contains(fn ($network): string | bool | null => $network->external);
        $hasExternalConfigs  = $compose->configs->contains(fn ($config): string | bool | null => $config->external);
        $hasExternalSecrets  = $compose->secrets->contains(fn ($secret): string | bool | null => $secret->external);

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
