<?php

namespace spkm\isams\Exceptions;

class IsamsInstanceNotFound extends \Exception
{
    /**
     * TODO: (See below)
     * Currently offered to be used by the consuming project. When the exception is a 404.
     * But in time, we aim to re-write this package so that this exception will be
     * thrown by the package itself on each request made to the API.
     */
}
