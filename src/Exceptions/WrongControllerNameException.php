<?php

namespace LemonCMS\LaravelCrud\Exceptions;

use Exception;

class WrongControllerNameException extends BaseCrudException
{
    public function __construct($message, $code = 503, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
