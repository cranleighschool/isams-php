<?php

namespace spkm\isams;

/**
 * Example School class implementing Institution contract.
 */
class School implements \spkm\isams\Contracts\Institution
{
    public function getConfigName(): string
    {
        return 'cranleighSandbox';
    }
}
