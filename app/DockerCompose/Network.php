<?php

declare(strict_types = 1);

namespace Filaship\DockerCompose;

use Filaship\Contracts\DockerComposeComponentInterface;

final class Network implements DockerComposeComponentInterface
{
    public function __construct(
        public string $name,
        public ?string $driver = null,
        public array $driverOpts = [],
        public array $labels = [],
        public string | bool | null $external = null,
        public array $ipam = [],
        public array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'driver'      => $this->driver,
            'driver_opts' => $this->driverOpts,
            'labels'      => $this->labels,
            'external'    => $this->external,
            'ipam'        => $this->ipam,
            'extra'       => $this->extra,
        ], fn ($value) => $value !== null && $value !== []);
    }

    public static function fromArray(string $name, array $data): self
    {
        return new self(
            name: $name,
            driver: $data['driver'] ?? null,
            driverOpts: $data['driver_opts'] ?? [],
            labels: $data['labels'] ?? [],
            external: $data['external'] ?? null,
            ipam: $data['ipam'] ?? [],
            extra: array_diff_key($data, array_flip([
                'driver', 'driver_opts', 'labels', 'external', 'ipam',
            ]))
        );
    }
}
