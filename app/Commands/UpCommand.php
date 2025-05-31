<?php

declare(strict_types = 1);

namespace Filaship\Commands;

use Filaship\Concerns\CommandCommons;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

use LaravelZero\Framework\Commands\Command;

class UpCommand extends Command
{
    use CommandCommons;

    protected $signature = 'up
         {--d|detached : Run in detached mode}
         {--f|file=docker-compose.yml : Specify an alternate compose file}';

    protected $description = 'Start Docker containers';

    public function handle(): int
    {
        $this->getCurrentDirectory();

        $detached    = $this->getOptionFlag('detached', '-d');
        $composeFile = $this->getOptionWithDefault('file', 'docker-compose.yml');

        $command = "docker compose -f {$composeFile} up {$detached}";

        $process = spin(
            callback: fn () => $this->runProcess($command),
            message: 'Starting containers...',
        );

        if ($process->failed()) {
            error('Failed to start containers: ' . $process->errorOutput());

            return self::FAILURE;
        }

        info('Containers started successfully.');

        return self::SUCCESS;
    }
}
