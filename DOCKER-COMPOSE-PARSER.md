# Filaship Docker Compose Parser

A comprehensive Docker Compose YAML parser and manipulator built with PHP 8.2+ and Laravel Zero.

## Features

-   🐳 **Complete Docker Compose Support**: Parses all Docker Compose components (services, volumes, networks, configs, secrets)
-   🏗️ **Clean Architecture**: Interface-based design with proper type safety
-   🔧 **Flexible Build Configurations**: Supports both string and object build configurations
-   📊 **Rich Analysis**: Built-in analysis tools for Docker Compose files
-   ✨ **Modern PHP**: Uses PHP 8.2+ features like readonly properties and match expressions
-   🧪 **Well Tested**: Comprehensive test suite with Pest

## Installation

```bash
composer install
```

## Quick Start

### Basic Parsing

```php
use Filaship\DockerCompose\DockerCompose;

$dockerCompose = new DockerCompose();
$parsed = $dockerCompose->parse('/path/to/docker-compose.yaml');

// Access components
echo "Version: " . $parsed->version;
echo "Services count: " . $parsed->services->count();

// Get specific service
$webService = $parsed->getService('web');
if ($webService) {
    echo "Web service image: " . $webService->image;
}
```

### Creating Compose Programmatically

```php
use Filaship\DockerCompose\{DockerCompose, Service};
use Filaship\DockerCompose\Service\BuildConfig;
use Illuminate\Support\Collection;

$services = new Collection([
    'web' => new Service(
        name: 'web',
        image: 'nginx:alpine',
        ports: ['80:80'],
        volumes: ['./html:/var/www/html:ro']
    ),
    'app' => new Service(
        name: 'app',
        build: new BuildConfig(
            context: '.',
            dockerfile: 'Dockerfile',
            args: ['PHP_VERSION' => '8.2']
        ),
        environment: ['APP_ENV=production']
    )
]);

$compose = new DockerCompose(
    version: '3.8',
    services: $services
);

// Export back to YAML
echo $compose->toYaml();
```

## Command Line Usage

### Parse and Display

```bash
php filaship docker-compose:parse example-docker-compose.yaml
```

### Advanced Analysis

```bash
php filaship docker-compose:example example-docker-compose.yaml -v
```

## Supported Components

### Services

-   ✅ Image and build configurations
-   ✅ Ports, volumes, environment variables
-   ✅ Networks, dependencies, labels
-   ✅ Commands (string or array)
-   ✅ Health checks, restart policies
-   ✅ Custom properties via `extra` array

### Build Configuration

-   ✅ Context and Dockerfile paths
-   ✅ Build arguments and labels
-   ✅ Target and cache configuration
-   ✅ String shorthand support

### Volumes

-   ✅ Driver and driver options
-   ✅ Labels and external volumes
-   ✅ Custom configurations

### Networks

-   ✅ Driver configurations
-   ✅ IPAM settings
-   ✅ External networks
-   ✅ Labels and custom options

### Configs & Secrets

-   ✅ File and external references
-   ✅ Labels and metadata
-   ✅ Docker Swarm compatibility

## Architecture

The library follows clean architecture principles:

```
DockerComposeComponentInterface
├── Service
├── Volume
├── Network
├── Config
└── Secret
```

All components implement the `DockerComposeComponentInterface` ensuring consistent:

-   `toArray()`: Serialization to array
-   `fromArray()`: Deserialization from array

## Advanced Usage

### Analyzing Compose Files

```php
use Filaship\Examples\DockerComposeUsageExample;

$example = new DockerComposeUsageExample();
$analysis = $example->parseAndAnalyze('/path/to/docker-compose.yaml');

// Get summary
$summary = $analysis['summary'];
echo "Total services: " . $summary['total_services'];

// Analyze services
$services = $analysis['services_analysis'];
$buildServices = $services['with_build']; // Services using build
$exposedPorts = $services['exposed_ports']; // Port mappings
```

### Modifying Existing Compose

```php
$dockerCompose = new DockerCompose();
$parsed = $dockerCompose->parse('/path/to/docker-compose.yaml');

// Add a new service
$monitoring = new Service(
    name: 'monitoring',
    image: 'prom/prometheus:latest',
    ports: ['9090:9090']
);

$parsed->services->put('monitoring', $monitoring);

// Save back to file
file_put_contents('/path/to/modified-compose.yaml', $parsed->toYaml());
```

## Testing

Run the test suite:

```bash
./vendor/bin/pest
```

Run specific tests:

```bash
./vendor/bin/pest tests/Unit/DockerComposeTest.php
```

## Examples

See the `app/Examples/DockerComposeUsageExample.php` file for comprehensive usage examples.

The `example-docker-compose.yaml` file contains a complex example showcasing all supported features.

## Requirements

-   PHP 8.2+
-   Composer
-   Symfony YAML component

## License

This project is part of the Filaship ecosystem.
