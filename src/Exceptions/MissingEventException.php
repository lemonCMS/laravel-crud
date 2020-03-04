<?php

namespace LemonCMS\LaravelCrud\Exceptions;

use Exception;

class MissingEventException extends Exception
{
    public function __construct($message, $code = 405, Exception $previous = null)
    {
        $message = 'Event could not be found: '.$message;

        parent::__construct($message, $code, $previous);
    }
}
