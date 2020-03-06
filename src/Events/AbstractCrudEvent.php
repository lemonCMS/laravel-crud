<?php

namespace LemonCMS\LaravelCrud\Events;

use Illuminate\Http\Request;

abstract class AbstractCrudEvent implements \JsonSerializable
{
    abstract public static function fromPayload($id, string $model, array $payload);

    abstract public static function rules(Request $request): array;

    abstract public function setId(string $id);

    abstract public function getId();
}
