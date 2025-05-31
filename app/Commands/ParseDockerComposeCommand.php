<?php

declare(strict_types = 1);

namespace Filaship\Commands;

use Filaship\DockerCompose\DockerCompose;
use Illuminate\Console\Command;

class ParseDockerComposeCommand extends Command
{
    protected $signature = 'docker-compose:parse {file : Path to docker-compose.yaml file}';

    protected $description = 'Parse a docker-compose.yaml file and display information';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return 1;
        }

        try {
            $dockerCompose = new DockerCompose();
            $parsed        = $dockerCompose->parse($filePath);

            $this->info("✅ File parsed successfully!");
            $this->line("");

            if ($parsed->version) {
                $this->line("📋 Version: {$parsed->version}");
            }

            if ($parsed->services->count() > 0) {
                $this->line("🐳 Services ({$parsed->services->count()}):");

                foreach ($parsed->services as $name => $service) {
                    $this->line("  - {$name}");

                    if ($service->image) {
                        $this->line("    Image: {$service->image}");
                    }

                    if (! empty($service->ports)) {
                        $this->line("    Ports: " . implode(', ', $service->ports));
                    }
                }
                $this->line("");
            }

            if ($parsed->volumes->count() > 0) {
                $this->line("💾 Volumes ({$parsed->volumes->count()}):");

                foreach ($parsed->volumes as $name => $volume) {
                    $this->line("  - {$name}");
                }
                $this->line("");
            }

            if ($parsed->networks->count() > 0) {
                $this->line("🌐 Networks ({$parsed->networks->count()}):");

                foreach ($parsed->networks as $name => $network) {
                    $this->line("  - {$name}");
                }
                $this->line("");
            }

            if ($parsed->configs->count() > 0) {
                $this->line("⚙️ Configs ({$parsed->configs->count()}):");

                foreach ($parsed->configs as $name => $config) {
                    $this->line("  - {$name}");
                }
                $this->line("");
            }

            if ($parsed->secrets->count() > 0) {
                $this->line("🔐 Secrets ({$parsed->secrets->count()}):");

                foreach ($parsed->secrets as $name => $secret) {
                    $this->line("  - {$name}");
                }
                $this->line("");
            }

            if ($this->option('verbose')) {
                $this->line("📄 Complete structure:");
                dump($parsed->toArray());
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Parse error: " . $e->getMessage());

            return 1;
        }
    }
}
