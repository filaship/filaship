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

    protected function dockerComposeFileExists(): bool
    {
        return file_exists($this->currentDirectory . '/docker-compose.yml') ||
               file_exists($this->currentDirectory . '/docker-compose.yaml');
    }

    /**
     * Get the Docker Compose file path
     */
    protected function getDockerComposeFile(): string
    {
        return $this->currentDirectory . '/docker-compose.yml';
    }

    /**
     * Check if a Docker Compose file exists and parse it
     */
    protected function getExistingDockerCompose(): ?\Filaship\DockerCompose\DockerCompose
    {
        if (! $this->dockerComposeFileExists()) {
            return null;
        }

        try {
            $parser = new \Filaship\DockerCompose\DockerCompose();

            return $parser->parse($this->getDockerComposeFile());
        } catch (\Exception $e) {
            error('❌ Error reading existing Docker Compose file: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Create base networks for Docker Compose
     */
    protected function createBaseNetworks(): \Illuminate\Support\Collection
    {
        return new \Illuminate\Support\Collection([
            'frontend' => new \Filaship\DockerCompose\Network(
                name: 'frontend',
                driver: 'bridge'
            ),
            'backend' => new \Filaship\DockerCompose\Network(
                name: 'backend',
                driver: 'bridge'
            ),
        ]);
    }

    /**
     * Collect environment variables interactively
     */
    protected function collectEnvironmentVariables(): array
    {
        $envVars = [];

        \Laravel\Prompts\note('Add environment variables (press Enter with empty value to finish)');

        while (true) {
            $key = \Laravel\Prompts\text(
                label: 'Environment variable name',
                placeholder: 'APP_ENV',
                hint: 'Leave empty to finish adding variables'
            );

            if (empty($key)) {
                break;
            }

            $value = \Laravel\Prompts\text(
                label: "Value for {$key}",
                placeholder: 'production',
                hint: 'Use ${VARIABLE} for external variables'
            );

            $envVars[] = "{$key}={$value}";
        }

        return $envVars;
    }
}
