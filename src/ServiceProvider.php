<?php

namespace LemonCMS\LaravelCrud;

use LemonCMS\LaravelCrud\Commands\CrudGenerator;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'config/crud.php' => config_path('crud.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravelCrud');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CrudGenerator::class,
            ]);
        }
    }

    public function register()
    {
    }
}
