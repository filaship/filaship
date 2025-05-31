<?php

declare(strict_types = 1);

namespace Filaship\Commands\DockerCompose;

use Filaship\DockerCompose\DockerCompose;
use Illuminate\Console\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;

class ParseDockerComposeCommand extends Command
{
    protected $signature = 'docker-compose:parse {file : Path to docker-compose.yaml file}';

    protected $description = 'Parse a docker-compose.yaml file and display information';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (! file_exists($filePath)) {
            error("File not found: {$filePath}");

            return 1;
        }

        try {
            $dockerCompose = new DockerCompose();
            $parsed        = $dockerCompose->parse($filePath);

            info("✅ File parsed successfully!");
            note("");

            if ($parsed->version !== null && $parsed->version !== '' && $parsed->version !== '0') {
                note("📋 Version: {$parsed->version}");
            }

            if ($parsed->services->count() > 0) {
                note("🐳 Services ({$parsed->services->count()}):");

                foreach ($parsed->services as $name => $service) {
                    note("  - {$name}");

                    if ($service->image) {
                        note("    Image: {$service->image}");
                    }

                    if ($service->ports !== []) {
                        note("    Ports: " . implode(', ', $service->ports));
                    }
                }
                note("");
            }

            if ($parsed->volumes->count() > 0) {
                note("💾 Volumes ({$parsed->volumes->count()}):");

                foreach ($parsed->volumes as $name => $volume) {
                    note("  - {$name}");
                }
                note("");
            }

            if ($parsed->networks->count() > 0) {
                note("🌐 Networks ({$parsed->networks->count()}):");

                foreach ($parsed->networks as $name => $network) {
                    note("  - {$name}");
                }
                note("");
            }

            if ($parsed->configs->count() > 0) {
                note("⚙️ Configs ({$parsed->configs->count()}):");

                foreach ($parsed->configs as $name => $config) {
                    note("  - {$name}");
                }
                note("");
            }

            if ($parsed->secrets->count() > 0) {
                note("🔐 Secrets ({$parsed->secrets->count()}):");

                foreach ($parsed->secrets as $name => $secret) {
                    note("  - {$name}");
                }
                note("");
            }

            if ($this->option('verbose')) {
                note("📄 Complete structure:");
                dump($parsed->toArray());
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Parse error: " . $e->getMessage());

            return 1;
        }
    }
}
