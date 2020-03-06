<?php

namespace LemonCMS\LaravelCrud\Exceptions;

use Exception;

class MissingModelException extends BaseCrudException
{
    public function __construct(string $resource, $code = 405, Exception $previous = null)
    {
        $message = 'Model for resource `' . $resource . '` could not be located, check if file exists and namespaces are correct';

        parent::__construct($message, $code, $previous);
    }
}
