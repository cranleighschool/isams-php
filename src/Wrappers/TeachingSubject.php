<?php

namespace spkm\isams\Wrappers;

use spkm\isams\Wrapper;

/**
 * Wrapper for the array returned by the iSAMS REST API endpoint.
 */
class TeachingSubject extends Wrapper
{
    /*
     * @var bool
     */
    protected $isHidden;

    /**
     * Handle the data.
     */
    protected function handle(): void
    {
        $this->isHidden = (bool) optional($this->item)->hidden;
    }
}
