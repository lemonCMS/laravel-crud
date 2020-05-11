<?php

namespace LemonCMS\LaravelCrud\Listeners;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LemonCMS\LaravelCrud\Events\AbstractCrudEvent;
use LemonCMS\LaravelCrud\Events\CrudEventLogger;

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
     * @var bool
     */
    protected $delete = false;
    /**
     * @var bool
     */
    protected $restore = false;

    /**
     * CrudListener constructor.
     * @param Request $request
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
    public function process(AbstractCrudEvent $event)
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

        $callback = function (Builder $query) {
            if (true === $this->restore) {
                $query->onlyTrashed();
            }
        };

        $this->entity = call_user_func([$this->model, $this->resourceLoader], $event->getId(), $this->request, $callback)->first();

        $this->run();
        $this->logEvent();
    }

    private function run()
    {
        if (is_callable([$this, 'beforeRun'])) {
            call_user_func([$this, 'beforeRun']);
        }

        if ($this->delete === true) {
            $this->entity->delete();
            if (is_callable([$this, 'afterDelete'])) {
                Log::debug('Running  afterDelete');
                call_user_func([$this, 'afterDelete']);
                Log::debug('Finished  afterDelete');
            }
            $this->sendResponse($this->entity, 200);

            return;
        }

        if ($this->restore === true) {
            $this->entity->restore();
            if (is_callable([$this, 'afterRestore'])) {
                Log::debug('Running  afterRestore');
                call_user_func([$this, 'afterRestore']);
                Log::debug('Finished  afterRestore');
            }
            $this->sendResponse($this->entity, 200);

            return;
        }

        Log::debug('Listener: '.get_called_class());
        foreach ($this->event->toPayload() as $field => $value) {
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

        Log::debug('Searching  afterRun: ');
        if (is_callable([$this, 'afterRun'])) {
            Log::debug('Found  afterRun: ');
            call_user_func([$this, 'afterRun']);
            Log::debug('Finished  afterRun: ');
        }

        if ($this->entity->isClean()) {
            Log::debug('Entity is clean, skipping');
            $this->sendResponse($this->entity, 200);

            return;
        }

        Log::debug('Searching  beforeSave: ');
        if (is_callable([$this, 'beforeSave'])) {
            Log::debug('Running  beforeSave: ');
            call_user_func([$this, 'beforeSave']);
            Log::debug('Finished  beforeSave: ');
        }

        if ($this->entity->save()) {
            Log::debug('Entity is saved id: '.$this->entity->id);
            Log::debug('Searching  afterSave: ');
            if (is_callable([$this, 'afterSave'])) {
                Log::debug('Running  afterSave: ');
                call_user_func([$this, 'afterSave']);
                Log::debug('Finished  afterSave: ');
            }
            $this->sendResponse($this->entity, 201);
        } else {
            Log::debug('Searching  afterSaveFailed: ');
            if (is_callable([$this, 'afterSaveFailed'])) {
                Log::debug('Running  afterSaveFailed: ');
                call_user_func([$this, 'afterSaveFailed']);
                Log::debug('Finished  afterSaveFailed: ');
            }
            Log::error('Listener: '.__CLASS__);
            Log::error('Model: '.$this->model);
            Log::error('Entity is save failed ID: '.$this->entity->id);
            $this->sendResponse($this->entity, 400);
        }
    }

    /**
     * @param $response
     * @param int $statusCode
     */
    protected function sendResponse($response, $statusCode = 200): void
    {
        $this->response->setContent($response)
            ->header('Access-Control-Allow-Origin', '*')
            ->setStatusCode($statusCode)
            ->send();
    }

    /**
     * @param AbstractCrudEvent|null $event
     */
    public function logEvent(AbstractCrudEvent $event = null): void
    {
        if (null === $event && $this->event) {
            event(new CrudEventLogger(get_class($this->event), $this->event->jsonSerialize() + ['id' => $this->entity->id]));

            return;
        }

        event(new CrudEventLogger(get_class($event), $event->jsonSerialize()));
    }

    /**
     * @param bool $value
     */
    protected function setDelete(bool $value = false): void
    {
        $this->delete = $value;
    }

    /**
     * @param bool $value
     */
    protected function setRestore(bool $value = false): void
    {
        $this->restore = $value;
    }

    protected function beforeRun()
    {
    }

    protected function afterRun()
    {
    }

    protected function beforeSave()
    {
    }

    protected function afterSave()
    {
    }

    protected function afterSaveFailed()
    {
    }

    protected function afterDelete()
    {
    }

    protected function afterRestore()
    {
    }
}
