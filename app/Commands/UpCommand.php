<?php

namespace Filaship\Commands;

use Filaship\DockerCompose\DockerCompose;
use LaravelZero\Framework\Commands\Command;

class UpCommand extends Command
{
    protected $signature = 'up';

    protected $description = 'TODO: add a description for the UpCommand';

    public function handle(): int
    {
        dd(getcwd());

        return self::SUCCESS;
    }
}
