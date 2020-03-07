<?php

namespace TestApp\Events;

class BlogUpdateEvent extends BaseBlogEvent
{
    /**
     * @param $id
     * @param array $payload
     * @return BlogUpdateEvent
     */
    public static function fromPayload($id, string $model, array $payload): BaseBlogEvent
    {
        return new static(
            $id,
            $model,
            $payload['title'],
            $payload['description']
        );
    }
}
