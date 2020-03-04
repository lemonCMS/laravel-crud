<?php

namespace LemonCMS\LaravelCrud\Events;

use Illuminate\Http\Request;

abstract class AbstractCrudEvent
{
    abstract public static function fromPayload($id, array $payload);

    abstract public static function authorize(Request $request): bool;

    abstract public static function rules(Request $request): array;

    abstract public function setId(string $id);

    abstract public function getId();
}
