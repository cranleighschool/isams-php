<?php

namespace spkm\isams\Wrappers;

use spkm\isams\Wrapper;

/**
 * Wrapper for the array returned by the iSAMS REST API endpoint.
 */
class TeachingSubject extends Wrapper
{
    protected bool $isHidden;

    /**
     * Handle the data.
     *
     * @return void
     */
    protected function handle()
    {
        $this->isHidden = $this->item->hidden;
    }
}
