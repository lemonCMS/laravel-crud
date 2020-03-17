<?php

namespace TestApp;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use TestApp\Models\Blog;
use TestApp\Models\Policies\BlogPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Blog::class => BlogPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
