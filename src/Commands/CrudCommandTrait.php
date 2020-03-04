<?php

namespace LemonCMS\LaravelCrud\Commands;

use League\Flysystem\Exception;
use Str;

trait CrudCommandTrait
{
    /**
     * List of flat controllers.
     * @var array
     */
    protected $controllers = [];

    protected $events = [];

    protected $listeners = [];

    /**
     * Getting all controllers with their namespaces.
     *
     * @param array $json
     * @param array $data
     */
    private function parseJson(array $json, array $data = [])
    {
        foreach ($json as $route => $values) {
            $type = $values['type'] ?? 'action';
            switch ($type) {
                case 'group':
                case 'middleware':
                case 'namespace':
                    $tmpData['namespace'] = ($data['namespace'] ?? '').(! empty($data['namespace']) ? '\\' : '').($values['namespace'] ?? '');
                    $this->parseJson($values['routes'], $tmpData);
                    break;
                case 'action':
                    list($controller) = explode('@', $values['action']);
                    $this->pushController($controller, $values, $data);
                    break;
                case 'resource':
                    $controller = $values['controller'] ?? \Str::studly(\Str::plural($route)).'Controller';
                    $this->pushController($controller, $values, $data);
                    break;
            }
        }
    }

    /**
     * Create flat list of controllers
     * and their actions.
     *
     * @param $controller
     * @param $values
     * @param $data
     */
    private function pushController($controller, $values, $data)
    {
        $list = explode('\\', $controller);
        $controller = last($list);
        array_pop($list);

        $namespace = ($data['namespace'] ?? '').
            (! empty($data['namespace']) ? '\\' : '').
            (count($list) > 0 ? implode('\\', $list).'\\' : '');
        $index = $namespace.$controller;

        if (! isset($this->controllers[$index])) {
            $this->controllers[$index] = [];
        }

        $this->controllers[$index][] = $values +
            ['meta' => [
                'controller' => $controller,
                'namespace' => rtrim($namespace, '\\'),
                'path' => str_replace('\\', DIRECTORY_SEPARATOR, rtrim($namespace, '\\')),
            ]];
    }

    private function parseEvents()
    {
        foreach ($this->controllers as $controller => $data) {
            foreach ($data as $action) {
                $meta = $action['meta'];
                preg_match('/(.*)(Controller)$/i', $meta['controller'], $matches);
                if (! $matches[1]) {
                    throw new Exception('Controller is wrong');
                }

                $meta['namespace'] = $meta['namespace'].'\\'.$matches[1];
                $meta['path'] = $meta['path'].DIRECTORY_SEPARATOR.$matches[1];
                $meta['model'] = Str::studly(Str::singular($matches[1]));
                $meta['policy'] = Str::studly(Str::singular($matches[1])).'Policy';

                $this->pushEvent($action, $meta);
            }
        }
    }

    private function pushEvent($data, $meta)
    {
        if ($data['type'] === 'resource') {
            foreach ($data['actions'] ?? [] as $action) {
                if (! in_array($action['method'], ['post', 'put', 'delete', 'patch'])) {
                    continue;
                }

                $meta['event'] = Str::studly($action['action']).'Event';
                $meta['listener'] = Str::studly($action['action']).'Listener';

                $this->events[] = $meta;
            }

            if ($data['options']['only'] ?? null) {
                foreach ($data['options']['only'] as $action) {
                    if (in_array($action, ['store', 'update', 'delete', 'restore'])) {
                        $meta['event'] = Str::studly($action).'Event';
                        $meta['listener'] = Str::studly($action).'Listener';

                        $this->events[] = $meta;
                    }
                }
            } else {
                foreach (['store', 'update', 'delete', 'restore'] as $action) {
                    if (in_array($action, ['store', 'update', 'delete', 'restore'])) {
                        $meta['event'] = Str::studly($action).'Event';
                        $meta['listener'] = Str::studly($action).'Listener';

                        $this->events[] = $meta;
                    }
                }
            }
        }
    }
}
