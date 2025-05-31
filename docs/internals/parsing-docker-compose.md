# Docker Compose Parser

## Overview

The Filaship Docker Compose Parser is a comprehensive PHP designed for programmatic manipulation of Docker Compose YAML files. It provides full bidirectional conversion between YAML and PHP objects with complete type safety and validation.

### Key Capabilities

-   **Complete Docker Compose v3.8+ Support**: All services, volumes, networks, configs, and secrets
-   **Type-Safe Object Model**: Strong typing with PHP 8.2+ features
-   **Bidirectional Serialization**: Parse YAML → Objects → YAML with data integrity
-   **Validation & Error Handling**: Comprehensive validation with detailed error messages
-   **Extensible Architecture**: Interface-based design for custom components
-   **CLI Integration**: Ready-to-use commands for file manipulation
-   **Production Ready**: Tested, documented, and performance optimized

## Architecture & Design Patterns

### Interface-Driven Design

All components implement the `DockerComposeComponentInterface`:

```php
interface DockerComposeComponentInterface
{
    public function toArray(): array;
    public static function fromArray(string $name, array $data): self;
}
```

This ensures:

-   **Consistency**: All components have the same serialization interface
-   **Extensibility**: Easy to add new component types
-   **Testability**: Mockable interfaces for unit testing
-   **Type Safety**: Compile-time guarantees on component behavior

### Core Components Architecture

```
DockerCompose (Orchestrator)
├── Service[] (Container definitions)
│   └── BuildConfig (Build specifications)
├── Volume[] (Storage definitions)
├── Network[] (Network topologies)
├── Config[] (Configuration files)
└── Secret[] (Sensitive data)

```

### Data Flow Architecture

```
YAML File → Symfony\YAML → Array → Component Objects → Validation → Business Logic
                ↑                                                        ↓
            Serialization ← Array ← Component Objects ← Modification ← Processing
```

## Core Components Deep Dive

### 1. DockerCompose Class

The main orchestrator that handles the complete Docker Compose specification.

#### Properties & Methods

```php
class DockerCompose
{
    public readonly ?string $version;
    public readonly Collection $services;
    public readonly Collection $volumes;
    public readonly Collection $networks;
    public readonly Collection $configs;
    public readonly Collection $secrets;
    public readonly array $extra; // Custom x-* properties

    // Core methods
    public function parse(string $filePath): self
    public function toArray(): array
    public function toYaml(): string
    public function getService(string $name): ?Service
    public function addService(string $name, Service $service): self
    public function removeService(string $name): self
    public function validate(): array // Returns validation errors
}
```

#### Parsing Example

```php
use Filaship\DockerCompose\DockerCompose;

$parser = new DockerCompose();

$compose = $parser->parse('/path/to/docker-compose.yml');

// Access version
echo "Compose version: " . $compose->version; // "3.8"

// Iterate services
foreach ($compose->services as $name => $service) {
    echo "Service: {$name}, Image: {$service->image}\n";
}

// Get specific service
$webService = $compose->getService('web');
if ($webService) {
    echo "Web service ports: " . implode(', ', $webService->ports);
}
```

### 2. Service Class

Represents individual container services with complete Docker Compose service specification.

#### Properties

```php
class Service implements DockerComposeComponentInterface
{
    public readonly string $name;
    public readonly ?string $image;
    public readonly ?BuildConfig $build;
    public readonly ?string $container_name;
    public readonly array $ports;           // ["80:8080", "443:8443"]
    public readonly array $volumes;         // ["./data:/data", "cache:/tmp/cache"]
    public readonly array $environment;     // ["ENV=prod", "DEBUG=false"]
    public readonly array $depends_on;      // ["db", "redis"]
    public readonly array $networks;        // ["frontend", "backend"]
    public readonly array $labels;          // ["traefik.enable=true"]
    public readonly mixed $command;         // String or array
    public readonly ?string $working_dir;
    public readonly ?string $user;
    public readonly ?string $restart;       // "no", "always", "on-failure", "unless-stopped"
    public readonly array $extra;           // Custom properties
}
```

#### Advanced Service Configuration

```php
use Filaship\DockerCompose\Service;
use Filaship\DockerCompose\Service\BuildConfig;

// Creating a complex service
$apiService = new Service(
    name: 'api',
    build: new BuildConfig(
        context: './backend',
        dockerfile: 'Dockerfile.prod',
        args: [
            'NODE_ENV' => 'production',
            'API_VERSION' => '2.1.0'
        ],
        target: 'runtime',
        cache_from: ['node:18-alpine']
    ),
    environment: [
        'DATABASE_URL' => '${DATABASE_URL}',
        'REDIS_URL' => 'redis://cache:6379',
        'JWT_SECRET' => '${JWT_SECRET}',
        'LOG_LEVEL' => 'info'
    ],
    ports: [
        '3000:3000',
        '3001:3001' // Admin interface
    ],
    volumes: [
        './uploads:/app/uploads',
        'logs:/app/logs',
        '/etc/ssl/certs:/etc/ssl/certs:ro'
    ],
    depends_on: ['database', 'cache', 'migrations'],
    networks: ['backend', 'monitoring'],
    labels: [
        'traefik.enable=true',
        'traefik.http.routers.api.rule=Host(`api.company.com`)',
        'traefik.http.routers.api.tls=true',
        'traefik.http.services.api.loadbalancer.server.port=3000'
    ],
    restart: 'unless-stopped',
    working_dir: '/app',
    user: 'node:node'
);
```

### 3. BuildConfig Class

Handles complex Docker build configurations with full Dockerfile and build argument support.

#### Properties & Usage

```php
class BuildConfig
{
    public readonly string $context;
    public readonly ?string $dockerfile;
    public readonly array $args;
    public readonly array $labels;
    public readonly ?string $target;
    public readonly array $cache_from;
    public readonly array $cache_to;
    public readonly ?string $network;
    public readonly array $extra;

    // Static factory method
    public static function parse(mixed $buildData): ?self

    // Serialization
    public function toArray(): array
    public function serialize(): array // Optimized for YAML output
}
```

### 4. Volume Class

Manages persistent storage configurations with driver options and external volume support.

```php
class Volume implements DockerComposeComponentInterface
{
    public readonly string $name;
    public readonly ?string $driver;
    public readonly array $driver_opts;
    public readonly mixed $external;    // boolean or string
    public readonly array $labels;
    public readonly array $extra;
}

// Example: Database volume with specific driver
$dbVolume = new Volume(
    name: 'postgres_data',
    driver: 'local',
    driver_opts: [
        'type' => 'bind',
        'o' => 'bind',
        'device' => '/opt/postgres-data'
    ],
    labels: [
        'backup.policy' => 'daily',
        'encryption' => 'enabled'
    ]
);

// External volume reference
$sharedVolume = Volume::fromArray('shared_storage', [
    'external' => true,
    'labels' => ['tier' => 'shared']
]);

// NFS volume
$nfsVolume = Volume::fromArray('nfs_data', [
    'driver' => 'local',
    'driver_opts' => [
        'type' => 'nfs',
        'o' => 'addr=nfs.company.com,rw',
        'device' => ':/shared/data'
    ]
]);
```

### 5. Network Class

Defines network topologies with IPAM configuration and custom drivers.

```php
class Network implements DockerComposeComponentInterface
{
    public readonly string $name;
    public readonly ?string $driver;
    public readonly array $driver_opts;
    public readonly mixed $external;
    public readonly array $ipam;
    public readonly array $labels;
    public readonly array $extra;
}

// Custom bridge network
$frontendNet = new Network(
    name: 'frontend',
    driver: 'bridge',
    driver_opts: [
        'com.docker.network.bridge.name' => 'frontend-br',
        'com.docker.network.driver.mtu' => '1450'
    ],
    ipam: [
        'driver' => 'default',
        'config' => [
            [
                'subnet' => '172.20.0.0/16',
                'gateway' => '172.20.0.1',
                'ip_range' => '172.20.240.0/20'
            ]
        ]
    ],
    labels: [
        'environment' => 'production',
        'security.zone' => 'frontend'
    ]
);

// Overlay network for Swarm
$overlayNet = Network::fromArray('backend', [
    'driver' => 'overlay',
    'driver_opts' => [
        'encrypted' => 'true'
    ],
    'attachable' => true,
    'ipam' => [
        'config' => [['subnet' => '10.0.0.0/24']]
    ]
]);
```

## Practical Usage Examples

### Example 1: Parsing and Analyzing Existing Compose Files

```php
use Filaship\DockerCompose\DockerCompose;

function analyzeComposeFile(string $filePath): array
{
    $parser = new DockerCompose();
    $compose = $parser->parse($filePath);

    $analysis = [
        'metadata' => [
            'version' => $compose->version,
            'file_path' => $filePath,
            'parsed_at' => now()->toISOString()
        ],
        'services' => [
            'total' => $compose->services->count(),
            'with_build' => $compose->services->filter(fn($s) => $s->build !== null)->count(),
            'with_image' => $compose->services->filter(fn($s) => $s->image !== null)->count(),
            'with_ports' => $compose->services->filter(fn($s) => !empty($s->ports))->count(),
        ],
        'infrastructure' => [
            'volumes' => $compose->volumes->count(),
            'networks' => $compose->networks->count(),
            'configs' => $compose->configs->count(),
            'secrets' => $compose->secrets->count(),
        ],
        'security_analysis' => analyzeSecurityIssues($compose),
        'port_mappings' => extractPortMappings($compose),
        'dependencies' => buildDependencyGraph($compose)
    ];

    return $analysis;
}

function analyzeSecurityIssues(DockerCompose $compose): array
{
    $issues = [];

    foreach ($compose->services as $name => $service) {
        // Check for privileged containers
        if (isset($service->extra['privileged']) && $service->extra['privileged']) {
            $issues[] = "Service '{$name}' runs in privileged mode";
        }

        // Check for exposed sensitive ports
        foreach ($service->ports as $port) {
            if (str_contains($port, ':22') || str_contains($port, ':3306')) {
                $issues[] = "Service '{$name}' exposes potentially sensitive port: {$port}";
            }
        }

        // Check for root user
        if ($service->user === 'root' || $service->user === '0') {
            $issues[] = "Service '{$name}' runs as root user";
        }
    }

    return $issues;
}

function extractPortMappings(DockerCompose $compose): array
{
    $mappings = [];

    foreach ($compose->services as $name => $service) {
        foreach ($service->ports as $port) {
            if (preg_match('/^(\d+):(\d+)$/', $port, $matches)) {
                $mappings[$name][] = [
                    'host_port' => (int) $matches[1],
                    'container_port' => (int) $matches[2],
                    'protocol' => 'tcp'
                ];
            }
        }
    }

    return $mappings;
}

function buildDependencyGraph(DockerCompose $compose): array
{
    $graph = [];

    foreach ($compose->services as $name => $service) {
        $graph[$name] = [
            'depends_on' => $service->depends_on,
            'networks' => $service->networks
        ];
    }

    return $graph;
}
```

### Example 2: Programmatic Compose File Generation

```php
use Filaship\DockerCompose\{DockerCompose, Service, Volume, Network};
use Filaship\DockerCompose\Service\BuildConfig;
use Illuminate\Support\Collection;

function createMicroservicesStack(): DockerCompose
{
    // Create shared infrastructure
    $networks = new Collection([
        'frontend' => new Network(
            name: 'frontend',
            driver: 'bridge'
        ),
        'backend' => new Network(
            name: 'backend',
            driver: 'bridge',
            driver_opts: ['com.docker.network.driver.mtu' => '1450']
        ),
        'monitoring' => new Network(
            name: 'monitoring',
            driver: 'bridge'
        )
    ]);

    $volumes = new Collection([
        'postgres_data' => new Volume(
            name: 'postgres_data',
            driver: 'local'
        ),
        'redis_data' => new Volume(
            name: 'redis_data',
            driver: 'local'
        ),
        'prometheus_data' => new Volume(
            name: 'prometheus_data',
            driver: 'local'
        )
    ]);

    // Create services
    $services = new Collection();

    // Database service
    $services->put('database', new Service(
        name: 'database',
        image: 'postgres:15-alpine',
        environment: [
            'POSTGRES_DB=microservices',
            'POSTGRES_USER=admin',
            'POSTGRES_PASSWORD=${DB_PASSWORD}'
        ],
        volumes: ['postgres_data:/var/lib/postgresql/data'],
        networks: ['backend'],
        ports: ['5432:5432'],
        restart: 'unless-stopped'
    ));

    // Cache service
    $services->put('cache', new Service(
        name: 'cache',
        image: 'redis:7-alpine',
        volumes: ['redis_data:/data'],
        networks: ['backend'],
        command: 'redis-server --appendonly yes',
        restart: 'unless-stopped'
    ));

    // API Gateway
    $services->put('gateway', new Service(
        name: 'gateway',
        build: new BuildConfig(
            context: './gateway',
            dockerfile: 'Dockerfile',
            args: ['NODE_ENV' => 'production']
        ),
        environment: [
            'SERVICE_DISCOVERY_URL=http://consul:8500',
            'REDIS_URL=redis://cache:6379'
        ],
        ports: ['80:3000', '443:3443'],
        depends_on: ['cache'],
        networks: ['frontend', 'backend'],
        restart: 'unless-stopped'
    ));

    // User service
    $services->put('user-service', createMicroservice(
        name: 'user-service',
        context: './services/user',
        port: '3001',
        dependencies: ['database', 'cache']
    ));

    // Order service
    $services->put('order-service', createMicroservice(
        name: 'order-service',
        context: './services/order',
        port: '3002',
        dependencies: ['database', 'cache', 'user-service']
    ));

    // Monitoring stack
    $services->put('prometheus', new Service(
        name: 'prometheus',
        image: 'prom/prometheus:latest',
        volumes: [
            './monitoring/prometheus.yml:/etc/prometheus/prometheus.yml:ro',
            'prometheus_data:/prometheus'
        ],
        ports: ['9090:9090'],
        networks: ['monitoring', 'backend'],
        command: [
            '--config.file=/etc/prometheus/prometheus.yml',
            '--storage.tsdb.path=/prometheus',
            '--web.console.libraries=/etc/prometheus/console_libraries',
            '--web.console.templates=/etc/prometheus/consoles',
            '--storage.tsdb.retention.time=200h',
            '--web.enable-lifecycle'
        ]
    ));

    return new DockerCompose(
        version: '3.8',
        services: $services,
        volumes: $volumes,
        networks: $networks
    );
}

function createMicroservice(string $name, string $context, string $port, array $dependencies = []): Service
{
    return new Service(
        name: $name,
        build: new BuildConfig(
            context: $context,
            dockerfile: 'Dockerfile',
            args: [
                'NODE_ENV' => 'production',
                'SERVICE_NAME' => $name
            ]
        ),
        environment: [
            'SERVICE_NAME=' . $name,
            'DATABASE_URL=${DATABASE_URL}',
            'REDIS_URL=redis://cache:6379',
            'LOG_LEVEL=info'
        ],
        ports: ["{$port}:3000"],
        depends_on: $dependencies,
        networks: ['backend'],
        restart: 'unless-stopped',
        labels: [
            'traefik.enable=true',
            "traefik.http.routers.{$name}.rule=PathPrefix(`/{$name}`)",
            "traefik.http.services.{$name}.loadbalancer.server.port=3000"
        ]
    );
}

// Usage
$compose = createMicroservicesStack();
file_put_contents('./docker-compose.production.yml', $compose->toYaml());
```