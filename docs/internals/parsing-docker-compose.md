# Docker Compose Parser Implementation

## Overview

This document outlines the technical implementation of the Docker Compose YAML parser within the Filaship ecosystem. The parser provides programmatic access to Docker Compose configurations with full type safety and bidirectional serialization capabilities.

## Architecture

### Component Interface

All Docker Compose components implement `DockerComposeComponentInterface`:

```php
interface DockerComposeComponentInterface
{
    public function toArray(): array;
    public static function fromArray(string $name, array $data): self;
}
```

### Core Components

-   **DockerCompose**: Main orchestrator handling YAML parsing and component management
-   **Service**: Container service definitions with build configurations
-   **Volume**: Persistent storage configurations
-   **Network**: Network topology definitions
-   **Config**: External configuration file references
-   **Secret**: Sensitive data management (Docker Swarm)

## Implementation Details

### Service Build Configuration

Services support both simple string paths and complex build objects:

```yaml
# String format
build: ./app

# Object format
build:
  context: ./app
  dockerfile: Dockerfile.prod
  args:
    - BUILD_ENV=production
  target: runtime
```

### Type Handling

The parser handles Docker Compose's flexible typing:

-   **Commands**: String or array format
-   **Environment**: Array or object notation
-   **External Resources**: Boolean or string references
-   **Ports**: String or object mapping

### Error Handling

-   File validation with descriptive error messages
-   YAML syntax error propagation
-   Type coercion with fallback handling
-   Partial parsing support for malformed sections

## Usage Patterns

### Parsing Existing Files

```php
$parser = new DockerCompose();
$compose = $parser->parse('/path/to/docker-compose.yml');

// Access services
$webService = $compose->getService('web');
$databases = $compose->services->filter(fn($s) => str_contains($s->image ?? '', 'mysql'));

// Analyze configuration
$exposedPorts = $compose->services
    ->filter(fn($s) => !empty($s->ports))
    ->mapWithKeys(fn($s) => [$s->name => $s->ports]);
```

### Programmatic Construction

```php
$service = new Service(
    name: 'api',
    build: BuildConfig::fromArray([
        'context' => './api',
        'dockerfile' => 'Dockerfile',
        'args' => ['NODE_ENV' => 'production']
    ]),
    environment: [
        'DATABASE_URL' => '${DATABASE_URL}',
        'REDIS_URL' => 'redis://cache:6379'
    ],
    depends_on: ['database', 'cache']
);

$compose = new DockerCompose(
    version: '3.8',
    services: collect(['api' => $service])
);
```

### Serialization

```php
// Back to array structure
$config = $compose->toArray();

// Export to YAML
file_put_contents('output.yml', $compose->toYaml());
```

## Extension Points

### Custom Properties

All components support custom properties via the `extra` array:

```php
$service = Service::fromArray('app', [
    'image' => 'nginx',
    'x-custom-label' => 'value',
    'x-deployment' => ['replicas' => 3]
]);

// Custom properties accessible via $service->extra
```

## Integration

### Service Container

```php
// In FilashipServiceProvider
$this->app->singleton(DockerCompose::class, function () {
    return new DockerCompose();
});
```

### CLI Integration

```php
// Command example
class DeployCommand extends Command
{
    public function handle(DockerCompose $parser)
    {
        $compose = $parser->parse($this->argument('file'));

        $this->validateProduction($compose);
        $this->deploy($compose);
    }
}
```


