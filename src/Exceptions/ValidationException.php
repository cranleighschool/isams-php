<?php

namespace spkm\isams\Exceptions;

class ValidationException extends \Exception
{
    public function __construct($message = "")
    {
        $this->message = $message;
    }
}