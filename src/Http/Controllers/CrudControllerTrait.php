<?php

namespace LemonCMS\LaravelCrud\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LemonCMS\LaravelCrud\Exceptions\MissingEventException;
use LemonCMS\LaravelCrud\Exceptions\MissingListenerException;
use LemonCMS\LaravelCrud\Exceptions\WrongControllerNameException;
use LemonCMS\LaravelCrud\Http\Requests\CrudRequest;

trait CrudControllerTrait
{
    protected $namespacePrefix = [];

    protected $suffixes;

    protected $namespaces = [
    ];

    protected $events = [
    ];

    protected $listeners = [
    ];

    protected $requests = [
    ];

    public function __construct()
    {
        $this->namespacePrefix = config('crud.namespacePrefix', [
            'controllers' => 'App\Http\Controllers',
            'events' => 'App\Events',
            'models' => 'App\Models',
            'listeners' => 'App\Listeners',
            'requests' => 'App\Http\Requests',
        ]);

        $this->suffixes = config('crud.suffixes', [
            'controller' => 'Controller',
            'event' => 'Event',
            'model' => null,
            'listener' => 'Listener',
            'request' => 'Request',
        ]);

        $this->tryExtractNameFromClass();
    }

    private function tryExtractNameFromClass()
    {
        $bindingClassName = (last(explode('\\', get_called_class())));
        if (!preg_match('/(.*)(Controller)$/i', $bindingClassName, $matches)) {
            throw new WrongControllerNameException(
                'The Controller ' . $bindingClassName . ' must end with `Controller.php`');
        }

        $resource = config('crud.models.plural', false) ? Str::plural($matches[1]) : Str::singular($matches[1]);
        $controllerNamespace = str_replace('\\', '\\\\\\', $this->namespacePrefix['controllers']);

        preg_match('/' . $controllerNamespace . '(.*)' . $bindingClassName . '$/i', get_called_class(), $matches2);
        $resourceNamespace = $matches2[1];

        $this->namespaces = [
            'controllers' => $this->namespacePrefix['controllers'] . $resourceNamespace,
            'events' => $this->namespacePrefix['events'] . $resourceNamespace,
            'models' => $this->namespacePrefix['models'] . $resourceNamespace,
            'listeners' => $this->namespacePrefix['listeners'] . $resourceNamespace,
        ];

        $this->events = [
            'default' => $this->combine('events', $resource),
            'index' => $this->combine('events', $resource, 'index'),
            'show' => $this->combine('events', $resource, 'show'),
            'store' => $this->combine('events', $resource, 'store'),
            'update' => $this->combine('events', $resource, 'update'),
            'destroy' => $this->combine('events', $resource, 'destroy'),
            'restore' => $this->combine('events', $resource, 'restore'),
        ];

        $this->listeners = [
            'store' => $this->combine('listeners', $resource, 'store'),
            'update' => $this->combine('listeners', $resource, 'update'),
            'destroy' => $this->combine('listeners', $resource, 'destroy'),
            'restore' => $this->combine('listeners', $resource, 'restore'),
        ];

        if (!isset($this->model) || null === $this->model) {
            $this->model = $this->combine('models', $resource);
        }
    }

    private function combine($namespace, $resource, $type = null)
    {
        $namespacedPath =
            $this->namespaces[$namespace] .
            $resource .
            ($type ? Str::studly($type) : '') .
            $this->suffixes[Str::singular($namespace)];

        if (class_exists($namespacedPath)) {
            return $namespacedPath;
        }

        $path = $this->namespacePrefix[$namespace] .
            '\\' .
            $resource .
            ($type ? Str::studly($type) : '') .
            $this->suffixes[Str::singular($namespace)];

        if (class_exists($path)) {
            return $path;
        }

        return null;
    }

    public function index(CrudRequest $request)
    {
        $this->authorizeAndValidate($request, 'default');
        $this->_index($request);
    }

    public function authorizeAndValidate(CrudRequest $request, string $event)
    {
        $this->_authorize($request, $event);
        $this->_validate($request, $event);
    }

    public function _authorize(CrudRequest $request, string $event)
    {
        if (is_callable([$this->events[$event], 'authorize'])) {
            $authorized = call_user_func([$this->events[$event], 'authorize'], $request);
            if (!$authorized) {
                throw new AuthorizationException('You are not authorized to execute this action');
            }

            return true;
        }

        if ('default' === $event) {
            return true;
        }

        if (is_callable([$this->events['default'], 'authorize'])) {
            $authorized = call_user_func([$this->events['default'], 'authorize'], $request);
            if (!$authorized) {
                throw new AuthorizationException('You are not authorized to execute this action');
            }

            return true;
        }
    }

    public function _validate(CrudRequest $request, string $event)
    {
        if (is_callable([$this->events[$event], 'rules'])) {
            $validator = Validator::make($request->all(), call_user_func([$this->events[$event], 'rules'], $request));
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            return true;
        }

        if ('default' === $event) {
            return true;
        }

        if (is_callable([$this->events['default'], 'rules'])) {
            $validator = Validator::make($request->all(), call_user_func([$this->events['default'], 'rules'], $request));

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
    }

    public function _index(CrudRequest $request)
    {
        call_user_func([$this->model, 'paginatedResources'], $request, $this->isPrivate());
    }

    public function isPrivate()
    {
        return false;
    }

    public function store(CrudRequest $request)
    {
        $this->authorizeAndValidate($request, 'store');

        $this->_store($request);
    }

    protected function _store(CrudRequest $request)
    {
        if (!is_callable([$this->events['store'], 'fromPayload'])) {
            throw new MissingEventException($this->events['store']);
        }

        if (!is_callable([$this->listeners['store'], 'handle'])) {
            throw new MissingListenerException($this->listeners['store']);
        }

        event(call_user_func([$this->events['store'], 'fromPayload'], null, $request->all(), $request->user()));
    }

    public function show(CrudRequest $request, int $id)
    {
        $this->authorizeAndValidate($request, 'show');

        $this->_show($request, $id);
    }

    protected function _show(CrudRequest $request, int $id)
    {
        call_user_func([$this->model, 'viewResource'], $id, $request, $this->isPrivate());
    }

    public function update(CrudRequest $request, int $id)
    {
        $this->authorizeAndValidate($request, 'update');

        $this->_update($request, $id);
    }

    protected function _update(CrudRequest $request, int $id)
    {
        if (!is_callable([$this->events['update'], 'fromPayload'])) {
            throw new MissingEventException($this->events['update']);
        }

        if (!is_callable([$this->listeners['update'], 'handle'])) {
            throw new MissingListenerException($this->listeners['update']);
        }

        event(call_user_func([$this->events['update'], 'fromPayload'], $id, $request->all(), $request->user()));
    }

    public function destroy(CrudRequest $request, int $id)
    {
        $this->authorizeAndValidate($request, 'destroy');

        $this->_destroy($request, $id);
    }

    protected function _destroy(CrudRequest $request, int $id)
    {
        if (!is_callable([$this->events['destroy'], 'fromPayload'])) {
            throw new MissingEventException($this->events['destroy']);
        }

        if (!is_callable([$this->listeners['destroy'], 'handle'])) {
            throw new MissingListenerException($this->listeners['destroy']);
        }

        event(call_user_func([$this->events['destroy'], 'fromPayload'], $id, $request->all()));
    }

    public function restore(CrudRequest $request, int $id)
    {
        $this->authorizeAndValidate($request, 'restore');

        $this->_restore($request, $id);
    }

    protected function _restore(CrudRequest $request, int $id)
    {
        event(call_user_func([$this->events['destroy'], 'fromPayload'], $id, $request->all()));
    }
}
