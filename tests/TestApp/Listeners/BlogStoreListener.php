<?php

namespace TestApp\Listeners;

use LemonCMS\LaravelCrud\Listeners\CrudListener;
use TestApp\Events\BlogStoreEvent;

class BlogStoreListener extends CrudListener
{
    /**
     * @var BlogStoreEvent
     */
    protected $event;

    /**
     * @param BlogStoreEvent $event
     */
    public function handle(BlogStoreEvent $event)
    {
        $this->init($event);
    }

    public function beforeRun()
    {
        $this->entity->title = $this->event->getTitle();
        $this->entity->description = $this->event->getDescription();
    }

    public function afterSave()
    {
        // $this->entity->users()->attach(\Request::user()->id);
    }

    public function subscribe($events)
    {
        die('SUBSCRIBE');
    }
}
