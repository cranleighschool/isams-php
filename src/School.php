<?php

namespace spkm\isams;

use spkm\isams\Contracts\Institution;

/**
 * Example School class implementing Institution contract.
 */
class School implements Institution
{
    public function getConfigName(): string
    {
        return 'cranleighSandbox';
    }
}
