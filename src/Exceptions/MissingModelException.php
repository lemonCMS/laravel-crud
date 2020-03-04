<?php

namespace LemonCMS\LaravelCrud\Exceptions;

use Exception;

class MissingModelException extends Exception
{
    public function __construct($message, $code = 405, Exception $previous = null)
    {
        $message = 'Model could not be located, check if file exists and namespaces are correct';

        parent::__construct($message, $code, $previous);
    }
}
