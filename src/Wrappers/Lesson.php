<?php

namespace spkm\isams\Wrappers;

use spkm\isams\Wrapper;

/**
 * Wrapper for the array returned by the iSAMS REST API endpoint.
 */
class Lesson extends Wrapper
{
    /**
     * Handle the data.
     *
     * @return void
     */
    protected function handle(): void
    {
        unset($this->employeeId);
        $this->teacher = $this->employeeTitle . ' ' . $this->employeeSurname;
        unset($this->employeeTitle);
        unset($this->employeeSurname);
    }
}
