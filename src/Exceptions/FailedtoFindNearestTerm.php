<?php

namespace spkm\isams\Exceptions;

class FailedtoFindNearestTerm extends \Exception
{
    public function __construct($message = '')
    {
        $this->message = $message;
    }
}
