<?php
/**
 * Here lives the configuration.
 */

return [
    'namespacePrefix' => [
        'controllers' => 'TestApp\Http\Controllers',
        'events' => 'TestApp\Events',
        'models' => 'TestApp\Models',
        'listeners' => 'TestApp\Listeners',
        'requests' => 'TestApp\Http\Requests',
    ],

    'suffixes' => [
        'controller' => 'Controller',
        'event' => 'Event',
        'model' => null,
        'listener' => 'Listener',
        'request' => 'Request',
    ],

    'models' => [
        'plural' => false, // UsersTable vs UserTable
    ],
];
