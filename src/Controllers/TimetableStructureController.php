<?php

namespace spkm\isams\Controllers;

use Illuminate\Support\Collection;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\TimetableDay;
use spkm\isams\Wrappers\TimetableDayPart;
use spkm\isams\Wrappers\TimetableWeek;

class TimetableStructureController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/timetables/structure';
    }

    /**
     * Get the timetable structure.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $this->endpoint = $this->endpoint . '/';

        $response = $this->guzzle->request('GET', $this->endpoint, [
            'headers' => $this->getHeaders()
        ]);

        $decoded = json_decode($response->getBody()->getContents());

        $week = collect($decoded)['timetableWeeks'][0];

        $days = $week->timetableDays;
        $result = [];
        foreach ($days as $day) {
            $result[$day->name] = collect($day->periods)->map(function ($item) {
                return new TimetableDayPart($item);
            });
        }


        return collect($result)->map(function($item) {
            return new TimetableDay($item);
        });
    }
}
