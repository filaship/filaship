<?php

declare(strict_types = 1);

namespace Filaship\DockerCompose;

use Filaship\Contracts\DockerComposeComponentInterface;

class Volume implements DockerComposeComponentInterface
{
    /**
     * @param array<string> $driverOpts
     * @param array<string> $labels
     * @param mixed|null $external
     * @param array<string> $extra
     */
    public function __construct(
        public string $name,
        public ?string $driver = null,
        public array $driverOpts = [],
        public array $labels = [],
        public mixed $external = null,
        public array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'name'        => $this->name,
            'driver'      => $this->driver,
            'driver_opts' => $this->driverOpts,
            'labels'      => $this->labels,
            'external'    => $this->external,
            'extra'       => $this->extra,
        ], fn ($value): bool => $value !== null && $value !== []);
    }

    public static function fromArray(string $name, array $data): self
    {
        return new self(
            name: $name,
            driver: $data['driver'] ?? null,
            driverOpts: $data['driver_opts'] ?? [],
            labels: $data['labels'] ?? [],
            external: $data['external'] ?? null,
            extra: array_diff_key($data, array_flip([
                'driver', 'driver_opts', 'labels', 'external',
            ]))
        );
    }
}
