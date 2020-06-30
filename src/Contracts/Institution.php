<?php

namespace spkm\isams\Contracts;

interface Institution
{
    /**
     * Define the name used to identify this Schools entry in the config.
     */
    public function getConfigName();
}
