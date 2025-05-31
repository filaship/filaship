<?php

declare(strict_types = 1);

namespace Filaship\Concerns;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\error;

trait CommandCommons
{
    protected string $currentDirectory;

    public function getCurrentDirectory(): ?string
    {
        $currentDirectory = getcwd();

        if ($currentDirectory === false) {
            error('Failed to get the current working directory.');

            return null;
        }

        $this->currentDirectory = $currentDirectory;

        return $currentDirectory;
    }

    public function runProcess(string $command): ProcessResult
    {
        $process = Process::forever()
            ->path($this->currentDirectory)
            ->run($command);

        return $process;
    }

    /**
     * Get option with default value
     */
    public function getOptionWithDefault(string $option, $default = null)
    {
        return $this->option($option) ?? $default;
    }

    /**
     * Get option as a flag (returns a string flag if option is true)
     */
    public function getOptionFlag(string $option, string $flagValue): string
    {
        return $this->option($option) ? $flagValue : '';
    }
}
