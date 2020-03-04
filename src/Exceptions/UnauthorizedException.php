<?php

namespace LemonCMS\LaravelCrud\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function __construct($message, $code = 401, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
