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
        /**
         * Now we use just one ISAMS we need to rely on divisions to work out what school a staff member is in.
         * NB: Isams makes 'divisions' null if empty, but our code in Pastoral Module expects an empty collection.
         */
        if (isset($this->divisions)) {
            $this->divisions = collect($this->divisions);
        } else {
            $this->divisions = collect();
        }

        if (isset($this->customFields)) {
            $this->customFields = collect($this->customFields);
        } else {
            $this->customFields = collect();
        }
    }
}
