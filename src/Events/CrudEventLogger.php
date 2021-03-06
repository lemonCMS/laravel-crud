<?php

namespace LemonCMS\LaravelCrud\Events;

use Illuminate\Contracts\Queue\ShouldQueue;

class CrudEventLogger implements ShouldQueue
{
    /**
     * @var string
     */
    public $event;

    /**
     * @var array
     */
    public $payload;

    /**
     * LoggingEvent constructor.
     * @param string $event
     * @param array $payload
     */
    public function __construct(string $event, array $payload)
    {
        $this->event = $event;
        if (isset($payload['password'])) {
            $payload['password'] = '*********';
        }
        $this->payload = $payload + [
                'meta' => [
                    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? null,
                    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
                ],
            ];
    }
}
