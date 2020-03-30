<?php

namespace LemonCMS\LaravelCrud;

use Illuminate\Support\Facades\Event;
use LemonCMS\LaravelCrud\Commands\CrudControllers;
use LemonCMS\LaravelCrud\Commands\CrudEvents;
use LemonCMS\LaravelCrud\Commands\CrudGenerator;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/crud.php' => config_path('crud.php'),
            __DIR__.'/config/oauth.php' => config_path('oauth.php'),
        ]);

        $this->loadViewsFrom(__DIR__.'/resources/views', 'crud');
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/database/migrations');
            $this->loadViewsFrom(__DIR__.'/resources/views', 'laravelCrud');

            $this->commands([
                CrudGenerator::class,
                CrudEvents::class,
                CrudControllers::class,
            ]);
        }

        Event::listen('LemonCMS\LaravelCrud\Events\CrudEventLogger',
            'LemonCMS\LaravelCrud\Listeners\CrudLogListener');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/crud.php', 'crud'
        );
    }
}
