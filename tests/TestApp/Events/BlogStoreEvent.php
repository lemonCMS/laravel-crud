<?php

namespace TestApp\Events;

class BlogStoreEvent extends BaseBlogEvent
{
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
}
