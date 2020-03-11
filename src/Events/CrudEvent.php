<?php

namespace LemonCMS\LaravelCrud\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

abstract class CrudEvent extends AbstractCrudEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $id;
    public $model;

    public function __construct($id, $model)
    {
        $this->id = $id;
        $this->model = $model;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    public static function fromPayload($id, string $model, array $payload)
    {
        return new static($id, $model);
    }

    public static function rules(Request $request): array
    {
        return [];
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'model' => $this->getModel(),
            'payload' => $this->toPayload()
        ];
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->getId()
        ];
    }
}
