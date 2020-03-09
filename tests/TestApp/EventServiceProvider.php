<?php

namespace TestApp;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'TestApp\Events\BlogStoreEvent' => [
            'TestApp\Listeners\BlogStoreListener',
        ],
        'TestApp\Events\BlogUpdateEvent' => [
            'TestApp\Listeners\BlogUpdateListener',
        ],
        'TestApp\Events\BlogDestroyEvent' => [
            'TestApp\Listeners\BlogDestroyListener',
        ],
        'TestApp\Events\BlogRestoreEvent' => [
            'TestApp\Listeners\BlogRestoreListener',
        ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

    protected function discoverEventsWithin()
    {
        return [
            $this->app->path('Listeners'),
            realpath($this->app->basePath('../../../../tests/TestApp/Listeners')),
        ];
    }

//    public function boot()
//    {
//        parent::boot();
//
//        Event::listen('*', function ($eventName, array $data) {
//            echo ($eventName."\r\n");
//            echo ($eventName) . "\r\n";
//            return true;
//        });
//    }
}
