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
            if (isset($this->$field)) {
                if (! is_null($this->$field)) {
                    $this->$field = Carbon::parse($this->$field);
                }
            }
        }

        if (isset($this->customFields)) {
            $this->customFields = collect($this->customFields);
        } else {
            $this->customFields = collect();
        }
    }
}
