<?php

declare(strict_types = 1);

namespace Filaship\Enums;

enum ServiceCategories: string
{
    case DATABASE   = 'database';
    case CACHE      = 'cache';
    case MONITORING = 'monitoring';
    case MAIL       = 'mail';
    case STORAGE    = 'storage';
    case SEARCH     = 'search';
    case TOOL       = 'tool';

    public function label(): string
    {
        return match ($this) {
            self::DATABASE   => 'Database',
            self::CACHE      => 'Cache',
            self::MONITORING => 'Monitoring',
            self::MAIL       => 'Mail',
            self::STORAGE    => 'Storage',
            self::SEARCH     => 'Search',
            self::TOOL       => 'Tool',
        };
    }

    public function allowMultiSelection(): bool
    {
        return match ($this) {
            self::DATABASE => false,
            default        => true,
        };
    }
}
