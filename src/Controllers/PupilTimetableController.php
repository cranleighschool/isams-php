<?php

namespace spkm\isams\Controllers;

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
    public function show(string $schoolId)
    {
        $this->endpoint = $this->endpoint . '/' . $schoolId;
        $response = $this->guzzle->request('GET', $this->endpoint,
            ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        $timetable = collect($decoded->sets);

        $schedule = new TimetableStructureController($this->institution);

        $result = [];
        foreach ($schedule->index() as $day => $days) {
            foreach ($days as $period) {
                $lesson = $timetable->filter(function ($item) use ($period) {
                    return $item->periodId === $period->id;
                });
                $period->lesson = $lesson;
                $result[$day][] = $period;
            }
        }

        return collect($result)->map(function ($item) {
            return new PupilTimetable($item);
        });
    }
}
