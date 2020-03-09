<?php

namespace LemonCMS\LaravelCrud\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LemonCMS\LaravelCrud\Exceptions\MissingEventException;
use LemonCMS\LaravelCrud\Exceptions\MissingListenerException;
use LemonCMS\LaravelCrud\Exceptions\MissingModelException;
use LemonCMS\LaravelCrud\Exceptions\WrongControllerNameException;

trait CrudControllerTrait
{
    public $model = null;
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
        $this->namespacePrefix = config('crud.namespacePrefix', []) + [
                'controllers' => 'App\Http\Controllers',
                'events' => 'App\Events',
                'models' => 'App\Models',
                'policies' => 'App\Models\Policies',
                'listeners' => 'App\Listeners',
                'requests' => 'App\Http\Requests',
            ];

        $this->suffixes = config('crud.suffixes', []) + [
                'controller' => 'Controller',
                'event' => 'Event',
                'model' => null,
                'policy' => 'Policy',
                'listener' => 'Listener',
                'request' => 'Request',
            ];

        $this->tryExtractNameFromClass();
    }

    private function tryExtractNameFromClass()
    {
        $this->initCrud();
        $bindingClassName = (last(explode('\\', get_called_class())));
        if (! preg_match('/(.*)(Controller)$/i', $bindingClassName, $matches)) {
            throw new WrongControllerNameException(
                'The Controller '.$bindingClassName.' must end with `Controller.php`');
        }

        $resource = Str::singular($matches[1]);
        $controllerNamespace = str_replace('\\', '\\\\\\', $this->namespacePrefix['controllers']);

        preg_match('/'.$controllerNamespace.'(.*)'.$bindingClassName.'$/i', get_called_class(), $matches2);
        $resourceNamespace = $matches2[1];

        $this->namespaces = [
            'controllers' => $this->namespacePrefix['controllers'].$resourceNamespace,
            'events' => $this->namespacePrefix['events'].$resourceNamespace,
            'models' => $this->namespacePrefix['models'].$resourceNamespace,
            'policies' => $this->namespacePrefix['policies'].$resourceNamespace,
            'listeners' => $this->namespacePrefix['listeners'].$resourceNamespace,
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

        if (! isset($this->model) || null === $this->model) {
            $this->model = $this->combine('models', $resource);
        }

        if (null === $this->model) {
            throw new MissingModelException($resource);
        }

        if (! isset($this->policy) || null === $this->policy) {
            $this->policy = $this->combine('policies', $resource);
        }
    }

    protected function initCrud()
    {
    }

    /**
     * @param $namespace
     * @param $resource
     * @param null $type
     * @return string|null
     */
    private function combine($namespace, $resource, $type = null)
    {
        $namespacedPath =
            $this->namespaces[$namespace].
            $resource.
            ($type ? Str::studly($type) : '').
            $this->suffixes[Str::singular($namespace)];

        if (class_exists($namespacedPath)) {
            return $namespacedPath;
        }

        $path = $this->namespacePrefix[$namespace].
            '\\'.
            $resource.
            ($type ? Str::studly($type) : '').
            $this->suffixes[Str::singular($namespace)];

        if (class_exists($path)) {
            return $path;
        }
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $this->runPolicy('viewAny');
        $this->_validate($request, 'default');
        $this->_index($request);
    }

    /**
     * @param $type
     * @param Model|null $record
     */
    protected function runPolicy($type, Model $record = null): void
    {
        if (! $this->usePolicies()) {
            return;
        }

        if (is_callable([$this->policy, $type])) {
            $this->authorize($type, ($record ?: $this->model));

            return;
        }

        $type = 'default';
        if (is_callable([$this->policy, $type])) {
            $this->authorize($type, ($record ?: $this->model));
        }
    }

    /**
     * @return bool
     */
    protected function usePolicies()
    {
        return true;
    }

    /**
     * @param Request $request
     * @param string $event
     * @return bool
     * @throws ValidationException
     */
    public function _validate(Request $request, string $event)
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

    /**
     * @param Request $request
     */
    public function _index(Request $request)
    {
        call_user_func([$this->model, 'paginatedResources'], $request, $this->getCallback());
    }

    /**
     * @return \Closure
     */
    protected function getCallback()
    {
        return function (Builder $query) {
            return $this->withQuery($query);
        };
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    protected function withQuery(Builder $query)
    {
        return $query;
    }

    /**
     * @param Request $request
     * @param int|string $id
     */
    public function show(Request $request, $id)
    {
        $record = call_user_func([$this->model, 'find'], $id);
        $this->runPolicy('view', $record);

        $this->_show($request, $id);
    }

    /**
     * @param Request $request
     * @param int|string $id
     */
    protected function _show(Request $request, $id)
    {
        call_user_func([$this->model, 'viewResource'], $id, $request, $this->getCallback());
    }

    /**
     * @param Request $request
     * @throws MissingEventException
     * @throws MissingListenerException
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->runPolicy('create');

        $this->_validate($request, 'store');

        $this->_store($request);
    }

    /**
     * @param Request $request
     * @throws MissingEventException
     * @throws MissingListenerException
     */
    protected function _store(Request $request)
    {
        $this->checkPipeline('store');

        event(call_user_func([$this->events['store'], 'fromPayload'],
            null,
            $this->model,
            $request->all(),
            $request->user()
        ));
    }

    /**
     * @param Request $request
     * @param int|string $id
     * @throws MissingEventException
     * @throws MissingListenerException
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $record = call_user_func(
            [$this->model, 'resource'],
            $id,
            $request,
            $this->getCallback()
        )->firstOrFail();

        $this->runPolicy('update', $record);

        $this->_validate($request, 'update');
        $this->_update($request, $id);
    }

    /**
     * @param Request $request
     * @param int|string $id
     * @throws MissingEventException
     * @throws MissingListenerException
     */
    protected function _update(Request $request, $id)
    {
        $this->checkPipeline('update');

        event(call_user_func(
            [$this->events['update'], 'fromPayload'],
            $id,
            $this->model,
            $request->all()
        ));
    }

    /**
     * @param Request $request
     * @param int|string $id
     * @throws MissingEventException
     * @throws MissingListenerException
     * @throws ValidationException
     */
    public function destroy(Request $request, $id)
    {
        $record = call_user_func(
            [$this->model, 'resource'],
            $id,
            $request,
            $this->getCallback()
        )->firstOrFail();
        $this->runPolicy('delete', $record);

        $this->_validate($request, 'destroy');
        $this->_destroy($request, $id);
    }

    /**
     * @param Request $request
     * @param int|string $id
     * @throws MissingEventException
     * @throws MissingListenerException
     */
    protected function _destroy(Request $request, $id)
    {
        $this->checkPipeline('destroy');

        event(call_user_func([$this->events['destroy'], 'fromPayload'], $id, $this->model, $request->all(), $this->getCallback()));
    }

    /**
     * @param Request $request
     * @param int|string $id
     * @throws ValidationException
     */
    public function restore(Request $request, $id)
    {
        $callback = function (Builder $query) {
            $query->onlyTrashed();
            $this->getCallback()($query);
        };

        $record = call_user_func(
            [$this->model, 'resource'],
            $id,
            $request,
            $callback
        )->firstOrFail();

        $this->runPolicy('restore', $record);

        $this->_validate($request, 'restore');

        $this->_restore($request, $id);
    }

    /**
     * @param Request $request
     * @param int|string $id
     */
    protected function _restore(Request $request, $id)
    {
        event(call_user_func([$this->events['restore'], 'fromPayload'], $id, $this->model, $request->all(), $this->getCallback()));
    }

    /**
     * Check if events and listeners are callable.
     *
     * @param $type
     * @throws MissingEventException
     * @throws MissingListenerException
     */
    private function checkPipeline($type)
    {
        if (! is_callable([$this->events[$type], 'fromPayload'])) {
            throw new MissingEventException($this->events[$type]);
        }

        if (! is_callable([$this->listeners[$type], 'handle'])) {
            throw new MissingListenerException($this->listeners[$type]);
        }
    }

    protected function setModel(string $model)
    {
        $this->model = $model;
    }
}
