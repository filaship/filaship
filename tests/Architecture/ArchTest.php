<?php

declare(strict_types = 1);

arch()
    ->expect('Filaship')
    ->toUseStrictTypes()
    ->not->toUse(['die', 'dd', 'dump', 'var_dump', 'print_r', 'ds', 'ray']);

arch()
    ->expect('Filaship\Contracts')
    ->toBeInterfaces();

arch()
    ->expect('Filaship\Concerns')
    ->toBeTraits();

arch()
    ->expect('Filaship\Commands')
    ->toBeClasses()
    ->toExtend('LaravelZero\Framework\Commands\Command');

arch()
    ->expect('Filaship\Services\**')
    ->toBeClasses()
    ->toExtend('Filaship\Services\BaseService');

arch()
    ->preset()
    ->php();

arch()
    ->preset()
    ->security();
