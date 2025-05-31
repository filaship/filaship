<?php

declare(strict_types = 1);

namespace Filaship\Contracts;

interface DockerComposeComponentInterface
{
    public function toArray(): array;

    public static function fromArray(string $name, array $data): self;
}
