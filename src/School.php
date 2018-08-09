<?php

namespace spkm\isams;

/**
 * Example School class implementing Institution contract.
 */
class School implements \spkm\isams\Contracts\Institution
{
    /**
     * @return string
     */
    public function getConfigName()
    {
        return 'cranleighSandbox';
    }
}