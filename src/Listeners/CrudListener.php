<?php

namespace LemonCMS\LaravelCrud\Listeners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LemonCMS\LaravelCrud\Events\AbstractCrudEvent;
use LemonCMS\LaravelCrud\Events\CrudEventLogger;
use LemonCMS\LaravelCrud\Http\Requests\CrudRequest;

abstract class CrudListener
{
    /**
     * @var string
     */
    public $className;
    /**
     * @var null
     */
    protected $model = null;
    /**
     * @var string
     */
    protected $resourceLoader = 'resource';
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var AbstractCrudEvent
     */
    protected $event;
    /**
     * @var Model
     */
    protected $entity = null;

    /**
     * CrudListener constructor.
     * @param CrudRequest $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->className = __CLASS__;
    }

    /**
     * @param AbstractCrudEvent $event
     */
    public function init(AbstractCrudEvent $event)
    {
        $this->event = $event;

        if (null === $this->model) {
            $this->model = $this->event->getModel() ?: null;
        }

        if (null === $this->model) {
            return;
        }

        if (null === $event->getId()) {
            $this->entity = new $this->model();
            $this->run();
            $this->logEvent();

            return;
        }
        $this->entity = call_user_func([$this->model, $this->resourceLoader], $event->getId(), $this->request, true)->first();

        $this->run();
        $this->logEvent();
    }

    private function run()
    {
        if (is_callable([$this, 'beforeRun'])) {
            call_user_func([$this, 'beforeRun']);
        }

        Log::debug('Listener: '.__CLASS__);
        foreach ($this->event->jsonSerialize() as $field => $value) {
            $method = Str::camel('set_'.$field);
            Log::debug('Searching method: '.$method);
            if (! is_callable([$this, $method])) {
                Log::debug('Method not found: '.$method);
                continue;
            }
            Log::debug('Method found: '.$method);
            call_user_func([$this, $method], $value);
            Log::debug('Entity is dirty: '.$this->entity->isDirty());
        }

        Log::debug('Searching  afterRun: '.$method);
        if (is_callable([$this, 'afterRun'])) {
            Log::debug('Found  afterRun: '.$method);
            call_user_func([$this, 'afterRun']);
            Log::debug('Finished  afterRun: '.$method);
        }

        if ($this->entity->isClean()) {
            Log::debug('Entity is clean, skipping');
            $this->response->json($this->entity, 200);

            return;
        }
        Log::debug('Searching  beforeSave: '.$method);
        if (is_callable([$this, 'beforeSave'])) {
            Log::debug('Running  beforeSave: '.$method);
            call_user_func([$this, 'beforeSave']);
            Log::debug('Finished  beforeSave: '.$method);
        }

        if ($this->entity->save()) {
            Log::debug('Entity is saved id: '.$this->entity->id);
            Log::debug('Searching  afterSave: '.$method);
            if (is_callable([$this, 'afterSave'])) {
                Log::debug('Running  afterSave: '.$method);
                call_user_func([$this, 'afterSave']);
                Log::debug('Finished  afterSave: '.$method);
            }
            $this->response($this->entity, 201);
        } else {
            Log::debug('Searching  afterSaveFailed: '.$method);
            if (is_callable([$this, 'afterSaveFailed'])) {
                Log::debug('Running  afterSaveFailed: '.$method);
                call_user_func([$this, 'afterSaveFailed']);
                Log::debug('Finished  afterSaveFailed: '.$method);
            }
            Log::error('Listener: '.__CLASS__);
            Log::error('Model: '.$this->model);
            Log::error('Entity is save failed ID: '.$this->entity->id);
            $this->response($this->entity, 400);
        }
    }

    /**
     * @param $response
     * @param int $statusCode
     */
    protected function response($response, $statusCode = 200): void
    {
        $this->response->setContent($response)
            ->setStatusCode($statusCode)
            ->send();
    }

    /**
     * @param AbstractCrudEvent|null $event
     */
    public function logEvent(AbstractCrudEvent $event = null)
    {
        if (null === $event && $this->event) {
            event(new CrudEventLogger(get_class($this->event), $this->event->jsonSerialize() + ['id' => $this->entity->id]));

            return;
        }

        event(new CrudEventLogger(get_class($event), $event->jsonSerialize()));
    }
}
