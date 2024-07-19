<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\TimetableControllerTrait;

/**
 * Class PupilTimetableController.
 */
class PupilTimetableController extends Endpoint
{
    use TimetableControllerTrait;

    /**
     * Set the URL the request is made to.
     *
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/timetables/students';
    }
}
