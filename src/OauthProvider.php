<?php

namespace LemonCMS\LaravelCrud;

use LemonCMS\LaravelCrud\Factories\OAuthClient;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class OauthProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        App::bind('OAuthClient', function()
        {
            return new OAuthClient;
        });
    }
}
