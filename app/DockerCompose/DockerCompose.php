<?php

declare(strict_types = 1);

namespace Filaship\DockerCompose;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

final class DockerCompose
{
    /**
     * @param Collection<string, Service> $services
     * @param Collection<string, Network> $networks
     * @param Collection<string, Volume> $volumes
     * @param Collection<string, Config> $configs
     * @param Collection<string, Secret> $secrets
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public ?string $version = null,
        public Collection $services = new Collection(),
        public Collection $networks = new Collection(),
        public Collection $volumes = new Collection(),
        public Collection $configs = new Collection(),
        public Collection $secrets = new Collection(),
        public array $extra = [],
    ) {
    }

    public function parse(string $filePath): self
    {
        if (! file_exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        try {
            $content = file_get_contents($filePath);
            $data    = Yaml::parse($content);
        } catch (\Exception $e) {
            throw new RuntimeException("Error parsing YAML file: " . $e->getMessage(), $e->getCode(), $e);
        }

        if (! is_array($data)) {
            throw new RuntimeException("Invalid YAML file content");
        }

        return $this->parseFromArray($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function parseFromArray(array $data): self
    {
        $services = new Collection();
        $networks = new Collection();
        $volumes  = new Collection();
        $configs  = new Collection();
        $secrets  = new Collection();

        // Parse services
        if (isset($data['services']) && is_array($data['services'])) {
            foreach ($data['services'] as $name => $serviceData) {
                $services->put($name, Service::fromArray($name, $serviceData));
            }
        }

        // Parse networks
        if (isset($data['networks']) && is_array($data['networks'])) {
            foreach ($data['networks'] as $name => $networkData) {
                $networks->put($name, Network::fromArray($name, $networkData ?? []));
            }
        }

        // Parse volumes
        if (isset($data['volumes']) && is_array($data['volumes'])) {
            foreach ($data['volumes'] as $name => $volumeData) {
                $volumes->put($name, Volume::fromArray($name, $volumeData ?? []));
            }
        }

        // Parse configs
        if (isset($data['configs']) && is_array($data['configs'])) {
            foreach ($data['configs'] as $name => $configData) {
                $configs->put($name, Config::fromArray($name, $configData ?? []));
            }
        }

        // Parse secrets
        if (isset($data['secrets']) && is_array($data['secrets'])) {
            foreach ($data['secrets'] as $name => $secretData) {
                $secrets->put($name, Secret::fromArray($name, $secretData ?? []));
            }
        }

        return new self(
            version: $data['version'] ?? null,
            services: $services,
            networks: $networks,
            volumes: $volumes,
            configs: $configs,
            secrets: $secrets,
            extra: array_diff_key($data, array_flip([
                'version', 'services', 'networks', 'volumes', 'configs', 'secrets',
            ]))
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'version'  => $this->version,
            'services' => $this->services->map(fn ($service): array => $service->toArray())->toArray(),
            'networks' => $this->networks->map(fn ($network): array => $network->toArray())->toArray(),
            'volumes'  => $this->volumes->map(fn ($volume): array => $volume->toArray())->toArray(),
            'configs'  => $this->configs->map(fn ($config): array => $config->toArray())->toArray(),
            'secrets'  => $this->secrets->map(fn ($secret): array => $secret->toArray())->toArray(),
            'extra'    => $this->extra,
        ], fn ($value): bool => $value !== null && $value !== []);
    }

    public function toYaml(): string
    {
        return Yaml::dump($this->toArray(), 4, 2);
    }

    public function getService(string $name): ?Service
    {
        return $this->services->get($name);
    }

    public function getNetwork(string $name): ?Network
    {
        return $this->networks->get($name);
    }

    public function getVolume(string $name): ?Volume
    {
        return $this->volumes->get($name);
    }

    public function getConfig(string $name): ?Config
    {
        return $this->configs->get($name);
    }

    public function getSecret(string $name): ?Secret
    {
        return $this->secrets->get($name);
    }

    public function hasService(string $name): bool
    {
        return $this->services->has($name);
    }

    /**
     * @return array<int, mixed>
     */
    public function getServiceNames(): array
    {
        return $this->services->keys()->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    public function getNetworkNames(): array
    {
        return $this->networks->keys()->toArray();
    }

    /**
     * @return array<int, mixed>
     */
    public function getVolumeNames(): array
    {
        return $this->volumes->keys()->toArray();
    }
}
