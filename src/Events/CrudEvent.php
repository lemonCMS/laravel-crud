<?php

namespace LemonCMS\LaravelCrud\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LemonCMS\LaravelCrud\Http\Requests\CrudRequest;

class CrudEvent extends AbstractCrudEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    public static function fromPayload($id, array $payload)
    {
        return new self($id);
    }

    public static function authorize(CrudRequest $request): bool
    {
        return true;
    }

    public static function rules(CrudRequest $request): array
    {
        return [];
    }
}
