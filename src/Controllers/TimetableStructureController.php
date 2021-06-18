<?php


namespace spkm\isams\Controllers;


use Illuminate\Support\Collection;
use spkm\isams\Endpoint;

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
     * Get the timetable for the specified pupil.
     *
     * @param string $schoolId
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $this->endpoint = $this->endpoint . '/';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        $week = collect($decoded)['timetableWeeks'][0];

        $days = $week->timetableDays;
        $frbDays = [];
        foreach ($days as $day) {
            $frbDays[$day->name] = collect($day->periods)->map(function($item) {
                unset($item->lastUpdated);
                unset($item->ordinal);
                unset($item->author);
                return $item;
            });
        }
        return collect($frbDays);
    }
}
