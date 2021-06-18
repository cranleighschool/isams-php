<?php

namespace spkm\isams\Wrappers;

use spkm\isams\Wrapper;

/**
 * Wrapper for the array returned by the iSAMS REST API endpoint.
 */
class TimetableDayPart extends Wrapper
{
    /**
     * Handle the data.
     *
     * @return void
     */
    protected function handle()
    {
        unset($this->lastUpdated);
        unset($this->ordinal);
        unset($this->author);
    }
}
