<?php

declare(strict_types = 1);

namespace Filaship\Concerns;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\error;

trait CommandCommons
{
    protected string $currentDirectory;

    protected function getCurrentDirectory(): ?string
    {
        $currentDirectory = getcwd();

        if ($currentDirectory === false) {
            error('Failed to get the current working directory.');

            return null;
        }

        $this->currentDirectory = $currentDirectory;

        return $currentDirectory;
    }

    protected function runProcess(string $command): ProcessResult
    {
        return Process::forever()
            ->path($this->currentDirectory)
            ->run($command);
    }

    /**
     * Get option with default value
     */
    protected function getOptionWithDefault(string $option, $default = null)
    {
        return $this->option($option) ?? $default;
    }

    /**
     * Get option as a flag (returns a string flag if option is true)
     */
    protected function getOptionFlag(string $option, string $flagValue): string
    {
        return $this->option($option) ? $flagValue : '';
    }
}
