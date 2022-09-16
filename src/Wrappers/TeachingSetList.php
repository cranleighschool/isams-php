<?php

namespace spkm\isams\Wrappers;

use spkm\isams\Wrapper;

/**
 * Wrapper for the array returned by the iSAMS REST API endpoint.
 */
class TeachingSetList extends Wrapper
{
    /*
     * @var bool
     */
    protected $isHidden;

    protected $item;

    /**
     * Handle the data.
     *
     * @return void
     */
    protected function handle(): void
    {
        $this->isHidden = (bool) optional($this->item)->hidden;
        $this->item->students = collect($this->students)->map(function ($item) {
            return $item->schoolId;
        });
        unset($this->students);
        $this->students = $this->item->students->toArray();
    }
}
