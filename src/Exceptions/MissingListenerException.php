<?php

namespace LemonCMS\LaravelCrud\Exceptions;

use Exception;

class MissingListenerException extends BaseCrudException
{
    public function __construct($message, $code = 405, Exception $previous = null)
    {
        $message = 'Listener could not be found: '.$message;

        parent::__construct($message, $code, $previous);
    }
}
