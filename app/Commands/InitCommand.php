<?php

declare(strict_types = 1);

namespace Filaship\Commands;

use Filaship\Concerns\CommandCommons;
use Filaship\DockerCompose\DockerCompose;
use Filaship\DockerCompose\Network;
use Filaship\Services\ServiceRegistry;
use Illuminate\Support\Collection;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

use LaravelZero\Framework\Commands\Command;

final class InitCommand extends Command
{
    use CommandCommons;

    protected $signature = 'init';

    protected $description = 'Initialize a new Laravel Docker Compose project with interactive setup';

    private DockerCompose $compose;

    private Collection $services;

    private Collection $volumes;

    private Collection $networks;

    private ServiceRegistry $serviceRegistry;

    private string $projectName = 'app';

    public function handle(): int
    {
        $this->getCurrentDirectory();

        // Initialize ServiceRegistry
        $this->serviceRegistry = new ServiceRegistry();

        // Initialize collections
        $this->services = new Collection();
        $this->volumes  = new Collection();
        $this->networks = new Collection();

        if ($this->dockerComposeFileExists()) {
            info('🐳 Docker Compose file already exists.');

            $action = select(
                label: 'What would you like to do?',
                options: [
                    'add'      => 'Add new services to existing file',
                    'recreate' => 'Recreate the entire file',
                    'cancel'   => 'Cancel and exit',
                ],
                default: 'add'
            );

            if ($action === 'cancel') {
                outro('Operation cancelled.');

                return self::SUCCESS;
            }

            if ($action === 'add') {
                return $this->addServicesToExisting();
            }
        }

        info('🚀 Welcome to Filaship!');

        $this->askForProjectInfo();
        $this->networks = $this->createBaseNetworks();
        $this->askForApplicationService();
        $this->askForServices();

        $this->createDockerComposeFile();

        outro('✅ Docker Compose file created successfully!');
        note('You can now run: filaship up');

        return self::SUCCESS;
    }

    private function addServicesToExisting(): int
    {
        try {
            $existingCompose = $this->getExistingDockerCompose();

            if (! $existingCompose) {
                return self::FAILURE;
            }

            info('📋 Current services: ' . implode(', ', $existingCompose->getServiceNames()));

            $this->services = $existingCompose->services;
            $this->volumes  = $existingCompose->volumes;
            $this->networks = $existingCompose->networks;

            // Initialize ServiceRegistry
            $this->serviceRegistry = new ServiceRegistry();

            $this->askForServices();
            $this->updateDockerComposeFile();

            outro('✅ Services added successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            error('❌ Error reading existing Docker Compose file: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function askForProjectInfo(): void
    {
        note('📋 Project Configuration');

        $projectName = text(
            label: 'What is your project name?',
            placeholder: 'laravel',
            hint: 'This will be used for container and volume naming',
            validate: fn (string $value) => match (true) {
                strlen($value) < 2                      => 'Project name must be at least 2 characters.',
                ! preg_match('/^[a-z0-9-_]+$/', $value) => 'Project name can only contain lowercase letters, numbers, hyphens and underscores.',
                default                                 => null
            }
        );

        // Store project name for later use
        $this->projectName = $projectName;
    }

    private function createBaseNetworks(): Collection
    {
        return new Collection([
            $this->projectName => new Network(
                name: $this->projectName,
                driver: 'bridge'
            ),
        ]);
    }

    private function askForApplicationService(): void
    {
        // TODO: Add Laravel Service implementation

        note('💡 Laravel service implementation coming soon...');
    }

    /**
     * Ask user to select and configure services using the service registry
     */
    private function askForServices(): void
    {
        note('🛠️ Laravel Infrastructure Services');

        // Get available service categories from ServiceRegistry
        $categories = $this->serviceRegistry->getCategories();

        $selectedCategories = multiselect(
            label: 'Which categories of services would you like to add?',
            options: $categories,
            hint: 'Select categories you want to include (use space to select, enter to confirm)'
        );

        foreach ($selectedCategories as $category) {
            $this->askForCategoryServices($category);
        }
    }

    /**
     * Ask user to select services from a specific category
     */
    private function askForCategoryServices(string $category): void
    {
        $services = $this->serviceRegistry->getServicesByCategory($category);

        if (empty($services)) {
            return;
        }

        $categoryName = match ($category) {
            'database'   => 'Database',
            'cache'      => 'Cache',
            'monitoring' => 'Monitoring',
            'mail'       => 'Email Testing',
            'storage'    => 'Storage',
            'search'     => 'Search Engine',
            'tool'       => 'Development Tool',
            default      => ucfirst($category)
        };

        note("📋 {$categoryName} Services");

        $serviceOptions = [];

        foreach ($services as $service) {
            $serviceOptions[$service->getName()] = $service->getDescription();
        }

        if ($category === 'database') {
            // For database, allow only one selection
            $selectedService = select(
                label: "Which {$categoryName} service would you like to use?",
                options: array_merge(['none' => 'None'], $serviceOptions),
                default: 'none',
                hint: 'Choose the database that best fits your application'
            );

            if ($selectedService !== 'none') {
                $this->addService($selectedService);
            }
        } else {
            // For other categories, allow multiple selections
            $selectedServices = multiselect(
                label: "Which {$categoryName} services would you like to add?",
                options: $serviceOptions,
                hint: 'Select services you want to include (use space to select, enter to confirm)'
            );

            foreach ($selectedServices as $serviceName) {
                $this->addService($serviceName);
            }
        }
    }

    /**
     * Add a service to the compose configuration
     */
    private function addService(string $serviceName): void
    {
        $serviceTemplate = $this->serviceRegistry->getService($serviceName);

        if (! $serviceTemplate) {
            error("Service '{$serviceName}' not found in registry.");

            return;
        }

        info("➕ Adding {$serviceTemplate->getDescription()}...");

        // Create the service
        $service = $serviceTemplate->createService();

        // Configure the service to use the project network
        $service->networks = [$this->projectName];

        // Add service to collection
        $this->services->put($serviceName, $service);

        // Add required volumes
        foreach ($serviceTemplate->getRequiredVolumes() as $volume) {
            $this->volumes->put($volume->name, $volume);
        }

        // Update app service dependencies if it exists
        $this->updateAppServiceDependencies($serviceName, $serviceTemplate);

        info("✅ {$serviceTemplate->getDescription()} added successfully!");
    }

    /**
     * Update app service with dependencies and environment variables
     */
    private function updateAppServiceDependencies(string $serviceName, $serviceTemplate): void
    {
        if (! $this->services->has('app')) {
            return;
        }

        $app = $this->services->get('app');

        // Add dependency
        $app->dependsOn = array_merge($app->dependsOn ?? [], [$serviceName]);

        // Add connection environment variable based on service type
        $category      = $serviceTemplate->getCategory();
        $connectionVar = match ($category) {
            'database' => $this->getDatabaseConnectionVar($serviceName, $serviceTemplate),
            'cache'    => $this->getCacheConnectionVar($serviceName, $serviceTemplate),
            default    => null
        };

        if ($connectionVar) {
            $app->environment = array_merge($app->environment ?? [], [$connectionVar]);
        }
    }

    /**
     * Get database connection environment variable
     */
    private function getDatabaseConnectionVar(string $serviceName, $serviceTemplate): ?string
    {
        return match ($serviceName) {
            'mysql'    => 'DATABASE_URL=mysql://user:password@mysql:3306/app',
            'postgres' => 'DATABASE_URL=postgresql://user:password@postgres:5432/app',
            'mongodb'  => 'DATABASE_URL=mongodb://admin:password@mongodb:27017/app',
            'mariadb'  => 'DATABASE_URL=mysql://user:password@mariadb:3306/app',
            default    => null
        };
    }

    /**
     * Get cache connection environment variable
     */
    private function getCacheConnectionVar(string $serviceName, $serviceTemplate): ?string
    {
        return match ($serviceName) {
            'redis'     => 'REDIS_URL=redis://redis:6379',
            'memcached' => 'MEMCACHED_URL=memcached://memcached:11211',
            default     => null
        };
    }

    private function createDockerComposeFile(): void
    {
        spin(
            message: 'Creating Docker Compose file...',
            callback: function () {
                $this->compose = new DockerCompose(
                    version: '3.8',
                    services: $this->services,
                    networks: $this->networks,
                    volumes: $this->volumes
                );

                $yaml = $this->compose->toYaml();
                file_put_contents($this->getDockerComposeFile(), $yaml);
            }
        );
    }

    private function updateDockerComposeFile(): void
    {
        spin(
            message: 'Updating Docker Compose file...',
            callback: function () {
                $this->compose = new DockerCompose(
                    version: '3.8',
                    services: $this->services,
                    networks: $this->networks,
                    volumes: $this->volumes
                );

                $yaml = $this->compose->toYaml();
                file_put_contents($this->getDockerComposeFile(), $yaml);
            }
        );
    }
}
