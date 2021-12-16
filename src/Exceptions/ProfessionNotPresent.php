<?php

namespace spkm\isams\Exceptions;

use Throwable;

class ProfessionNotPresent extends \Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        if (str_contains($message, 'is not present in the profession types list.')) {
        }
        parent::__construct($message, $code, $previous);
    }
}
