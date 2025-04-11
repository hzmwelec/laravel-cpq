<?php

namespace Hzmwelec\CPQ;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class CPQServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\CreateVersionCommand::class,
                Console\CreateProductCommand::class,
                Console\ImportFactorsCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrations();
            $this->publishImports();
            $this->publishMigrations();
        }
    }

    /**
     * @return void
     */
    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * @return void
     */
    protected function publishImports()
    {
        $this->publishes([
            __DIR__ . '/../database/imports' => App::databasePath('imports'),
        ], 'imports');
    }

    /**
     * @return void
     */
    protected function publishMigrations()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations' => App::databasePath('migrations'),
        ], 'migrations');
    }
}
