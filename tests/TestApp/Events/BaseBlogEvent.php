<?php

namespace TestApp\Events;

use Illuminate\Http\Request;
use LemonCMS\LaravelCrud\Events\CrudEvent;

class BaseBlogEvent extends CrudEvent
{
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $description;

    /**
     * AccountUpdate constructor.
     *
     * @param $id
     */
    public function __construct($id, string $model, string $title, string $description)
    {
        parent::__construct($id, $model);
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * @param $id
     * @param array $payload
     * @return BaseBlogEvent
     */
    public static function fromPayload($id, string $model, array $payload)
    {
        return new self(
            null,
            $model,
            $payload['title'],
            $payload['description']
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    public static function rules(Request $request): array
    {
        return [
            'title' => 'required',
            'description' => 'required',
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
        ];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
