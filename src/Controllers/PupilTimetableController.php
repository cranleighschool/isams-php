<?php

namespace spkm\isams\Controllers;

use Illuminate\Support\Collection;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\PupilTimetable;

class PupilTimetableController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/timetables/students';
    }

    /**
     * Get the timetable for the specified pupil.
     *
     * @param string $schoolId
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(string $schoolId): Collection
    {
        $this->endpoint = $this->endpoint . '/' . $schoolId;

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return collect($decoded)->map(function ($item) {
            return new PupilTimetable($item);
        });
    }
}
