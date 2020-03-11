<?php

namespace LemonCMS\LaravelCrud\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use League\Flysystem\Exception;
use Str;

trait CrudCommandTrait
{
    /**
     * Permission to overwrite all existing files.
     *
     * @var bool
     */
    protected $allConfirmed = null;

    /**
     * List of flat controllers.
     * @var array
     */
    protected $controllers = [];

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var array
     */
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
                    $controller = $values['controller'] ?? \Str::studly(\Str::plural($route)).config('crud.suffixes.controller');
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

    /**
     * Get events from flat controllers list.
     *
     * @throws Exception
     */
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
                $meta['model'] = Str::studly(config('crud.models.plural') ? Str::plural($matches[1]) : Str::singular($matches[1]));
                $meta['policy'] = Str::studly(Str::singular($matches[1])).config('crud.suffixes.policy');

                $this->pushEvent($action, $meta);
            }
        }
    }

    /**
     * Push found event on stack.
     *
     * @param $data
     * @param $meta
     */
    private function pushEvent($data, $meta)
    {
        if ($data['type'] === 'resource') {
            foreach ($data['actions'] ?? [] as $action) {
                if (! in_array($action['method'], ['post', 'put', 'delete', 'patch'])) {
                    continue;
                }

                $meta['event'] = Str::studly($action['action']).config('crud.suffixes.event');
                $meta['listener'] = Str::studly($action['action']).config('crud.suffixes.listener');

                $this->events[] = $meta;
            }

            if ($data['options']['only'] ?? null) {
                foreach ($data['options']['only'] as $action) {
                    if (in_array($action, ['store', 'update', 'delete', 'restore'])) {
                        $meta['event'] = Str::studly($action).'Event';
                        $meta['listener'] = Str::studly($action).config('crud.suffixes.listener');

                        $this->events[] = $meta;
                    }
                }
            } else {
                foreach (['store', 'update', 'delete', 'restore'] as $action) {
                    if (in_array($action, ['store', 'update', 'delete', 'restore'])) {
                        $meta['event'] = Str::studly($action).'Event';
                        $meta['listener'] = Str::studly($action).config('crud.suffixes.listener');

                        $this->events[] = $meta;
                    }
                }
            }
        }
    }

    /**
     * Handle user input
     * Available options are: y, yes, n, no, never, always.
     *
     *
     * @param $file
     * @return bool
     */
    private function getConfirmation($file)
    {
        if ($this->option('always')) {
            return true;
        }

        if ($this->option('never')) {
            return false;
        }

        if (true === $this->allConfirmed) {
            return true;
        }

        if (false === $this->allConfirmed) {
            return false;
        }

        do {
            $this->info('File: "'.$file.'" already exists');
            $answer = $this->anticipate('overwrite? (never, no, yes, always)',
                ['never', 'no', 'yes', 'all'], 'no');
            $answer = strtolower($answer);
        } while (! in_array($answer, ['never', 'no', 'yes', 'always', 'y', 'n', 'a']));

        if (in_array($answer, ['always', 'a'])) {
            $this->allConfirmed = true;

            return true;
        }

        if ('never' === $answer) {
            $this->allConfirmed = false;

            return false;
        }

        return in_array($answer, ['yes', 'y']);
    }

    /**
     * Load json configuration.
     *
     * @return mixed
     * @throws \Exception
     */
    protected function loadConfig()
    {
        $this->config = $this->option('config') ?: base_path('.crud-specs.json');
        if (! File::exists($this->config)) {
            throw new \Exception(new FileNotFoundException('File not found at '.$this->config));
        }
        $data = File::get($this->config);
        $json = json_decode($data, true);

        if ($this->isValidJson()) {
            return $json;
        }
    }

    /**
     * Check json for errors.
     *
     * @return bool
     * @throws \Exception
     */
    private function isValidJson()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return true;
                break;
            default:
            case JSON_ERROR_SYNTAX:
                throw new \Exception('Syntax error, malformed JSON');
                break;
        }
    }

    /**
     * Check if path exists if not then create it.
     *
     * @param $path
     * @return string
     * @throws \Exception
     */
    public function getPath($path)
    {
        if (is_array($path)) {
            $path = implode(DIRECTORY_SEPARATOR, $path);
        }

        if ($this->option('path')) {
            if (! realpath($this->option('path'))) {
                throw new \Exception('Directory not found : '.$this->option('path'));
            }

            $rp = implode(DIRECTORY_SEPARATOR, [realpath($this->option('path')), $path]);

            if (! \File::isDirectory($rp)) {
                \File::makeDirectory($rp, 493, true);
            }

            return $rp;
        }

        if (! \File::isDirectory(app_path($path))) {
            \File::makeDirectory(app_path($path), 493, true);
        }

        return app_path($path);
    }
}
