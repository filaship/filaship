<?php

declare(strict_types = 1);

namespace Filaship\Contracts;

interface DockerComposeComponentInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(string $name, array $data): self;
}
