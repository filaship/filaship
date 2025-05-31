<?php

declare(strict_types = 1);

namespace Filaship\DockerCompose\Service;

class BuildConfig
{
    public function __construct(
        public ?string $context = null,
        public ?string $dockerfile = null,
        public array $args = [],
        public array $labels = [],
        public ?string $target = null,
        public array $cacheFrom = [],
        public array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'context'    => $this->context,
            'dockerfile' => $this->dockerfile,
            'args'       => $this->args,
            'labels'     => $this->labels,
            'target'     => $this->target,
            'cache_from' => $this->cacheFrom,
            'extra'      => $this->extra,
        ], fn ($value) => $value !== null && $value !== []);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            context: $data['context'] ?? null,
            dockerfile: $data['dockerfile'] ?? null,
            args: $data['args'] ?? [],
            labels: $data['labels'] ?? [],
            target: $data['target'] ?? null,
            cacheFrom: $data['cache_from'] ?? [],
            extra: array_diff_key($data, array_flip([
                'context', 'dockerfile', 'args', 'labels', 'target', 'cache_from',
            ]))
        );
    }

    public static function fromString(string $context): self
    {
        return new self(context: $context);
    }
}
