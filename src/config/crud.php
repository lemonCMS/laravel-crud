<?php
/**
 * Here lives the configuration.
 */

return [
    'namespacePrefix' => [
        'controllers' => 'App\Http\Controllers',
        'events' => 'App\Events',
        'models' => 'App\Models',
        'listeners' => 'App\Listeners',
        'requests' => 'App\Http\Requests',
    ],

    'suffixes' => [
        'controller' => 'Controller',
        'event' => 'Event',
        'model' => 'Table',
        'listener' => 'Listener',
        'request' => 'Request',
        'policy' => 'Policy',
    ],

    'models' => [
        'plural' => true, // UsersTable vs UserTable
    ],
];
