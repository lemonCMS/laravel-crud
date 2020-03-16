<?php

namespace LemonCMS\LaravelCrud\Listeners;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use LemonCMS\LaravelCrud\Events\CrudEventLogger;
use LemonCMS\LaravelCrud\Model\EventsTable;

class CrudLogListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param CrudEventLogger $event
     * @return void
     */
    public function handle(CrudEventLogger $event)
    {
        $createdAt = Carbon::now();
        $payload = json_encode($event->payload);
        $checksum = env('APP_KEY').$event->event.$payload.$createdAt;

        // save in DB
        $entity = new EventsTable();
        $entity->event = $event->event;
        $entity->payload = $payload;
        $entity->created_at = $createdAt;
        $entity->checksum = bcrypt($checksum);
        $entity->save();
    }
}
