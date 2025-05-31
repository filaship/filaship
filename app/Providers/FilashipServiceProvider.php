<?php

declare(strict_types = 1);

namespace Filaship\Providers;

use Filaship\DockerCompose\DockerCompose;
use Illuminate\Support\ServiceProvider;

class FilashipServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DockerCompose::class, function ($app): DockerCompose {
            return new DockerCompose();
        });

        $this->app->alias(DockerCompose::class, 'docker-compose');
    }
}
