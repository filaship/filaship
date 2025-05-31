<?php

declare(strict_types = 1);

namespace Filaship\DockerCompose;

use Filaship\Contracts\DockerComposeComponentInterface;

final class Secret implements DockerComposeComponentInterface
{
    /**
     * @param array<string> $labels
     * @param array<string> $extra
     */
    public function __construct(
        public string $name,
        public ?string $file = null,
        public string | bool | null $external = null,
        public array $labels = [],
        public array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'name'     => $this->name,
            'file'     => $this->file,
            'external' => $this->external,
            'labels'   => $this->labels,
            'extra'    => $this->extra,
        ], fn ($value): bool => $value !== null && $value !== []);
    }

    public static function fromArray(string $name, array $data): self
    {
        return new self(
            name: $name,
            file: $data['file'] ?? null,
            external: $data['external'] ?? null,
            labels: $data['labels'] ?? [],
            extra: array_diff_key($data, array_flip([
                'file', 'external', 'labels',
            ]))
        );
    }
}
