<?php

namespace spkm\isams\Wrappers;

use Illuminate\Support\Carbon;
use spkm\isams\Wrapper;

/**
 * Wrapper for the array returned by the iSAMS REST API endpoint.
 */
class Employee extends Wrapper
{
    /**
     * Handle the data.
     *
     * @return void
     */
    protected function handle(): void
    {
        $fields = ['dateOfBirth', 'enrolmentDate'];
        foreach ($fields as $field) {
            if (! is_null($this->$field)) {
                $this->$field = Carbon::parse($this->$field);
            }
        }
        $this->customFields = collect($this->customFields);
    }
}
