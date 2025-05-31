<?php

declare(strict_types = 1);

namespace Filaship\DockerCompose\Service;

final class BuildConfig
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

    public static function parse(mixed $buildData): ?self
    {
        return match (true) {
            is_string($buildData) => self::fromString($buildData),
            is_array($buildData)  => self::fromArray($buildData),
            default               => null,
        };
    }

    public function serialize(): array | string
    {
        $array = $this->toArray();

        // If only context is set, return as string for cleaner YAML
        if (count($array) === 1 && isset($array['context'])) {
            return $array['context'];
        }

        return $array;
    }
}
