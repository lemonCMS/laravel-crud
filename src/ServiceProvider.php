<?php

namespace LemonCMS\LaravelCrud;

use LemonCMS\LaravelCrud\Commands\CrudController;
use LemonCMS\LaravelCrud\Commands\CrudEvents;
use LemonCMS\LaravelCrud\Commands\CrudGenerator;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'config/crud.php' => config_path('crud.php'),
            __DIR__.'resources/views' => base_path('resources/views/lemoncms/laravelcrud'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/database/migrations');
            $this->loadViewsFrom(__DIR__.'/resources/views', 'laravelCrud');

            $this->commands([
                CrudGenerator::class,
                CrudEvents::class,
                CrudController::class,
            ]);
        }
    }

    public function register()
    {
    }
}
