<?php

namespace LemonCMS\LaravelCrud\Events;

use LemonCMS\LaravelCrud\Http\Requests\CrudRequest;

abstract class AbstractCrudEvent
{
    abstract public static function fromPayload($id, array $payload);

    abstract public static function authorize(CrudRequest $request): bool;

    abstract public static function rules(CrudRequest $request): array;

    abstract public function setId(string $id);

    abstract public function getId();
}
