<?php

namespace App\Exceptions\Domain;

use Exception;

class EmailDispatchException extends Exception
{
    public $type;

    public function __construct($type, $message = "", $code = 0, \Throwable $previous = null)
    {
        $this->type = $type;
        parent::__construct($message, $code, $previous);
    }
}