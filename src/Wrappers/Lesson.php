<?php

namespace spkm\isams\Wrappers;

use Illuminate\Support\Collection;
use spkm\isams\Contracts\Institution;
use spkm\isams\Controllers\RoughAndReadyController;
use spkm\isams\Wrapper;

/**
 * Wrapper for the array returned by the iSAMS REST API endpoint.
 */
class Lesson extends Wrapper
{
    public function __construct($item, Institution $institution)
    {
        $this->institution = $institution;
        parent::__construct($item);
    }

    /**
     * Handle the data.
     */
    protected function handle(): void
    {
        if (isset($this->employeeId)) {
            unset($this->employeeId);
            $this->teacher = $this->employeeTitle.' '.$this->employeeSurname;
            unset($this->employeeTitle);
            unset($this->employeeSurname);
        } else {
            // Must be a teacher...
            $this->pupils = $this->getPupilsInSet($this->id);
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getPupilsInSet(int $setId): Collection
    {
        $api = new RoughAndReadyController($this->institution);

        return collect($api->get('teaching/sets/'.$setId.'/setList')->students)->pluck('schoolId');
    }
}
