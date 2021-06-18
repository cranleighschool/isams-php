<?php

namespace spkm\isams\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Lesson;
use spkm\isams\Wrappers\TimetableDay;

class PupilTimetableController extends Endpoint
{
    /**
     * Get the timetable for the specified pupil.
     *
     * @param  string  $schoolId
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(string $schoolId): Collection
    {
        $this->endpoint = $this->endpoint . '/' . $schoolId;
        $response = $this->guzzle->request('GET', $this->endpoint,
            ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        $timetable = collect($decoded->sets);

        $result = [];
        foreach ($this->getTimetableStructure() as $day => $days) {
            foreach ($days as $period) {
                $lesson = $timetable->filter(function ($item) use ($period) {
                    return $item->periodId === $period->id;
                })->map(function ($item) {
                    return new Lesson($item);
                })->first();
                if ($lesson) {
                    $subjectName = $this->getSubject($lesson->subjectId)->name;
                    $lesson->subjectName = $subjectName;
                }
                $period->lesson = $lesson;
                $result[$day][] = $period;
            }
        }

        return collect($result)->map(function ($item) {
            return new TimetableDay($item);
        });
    }

    /**
     * @param  int  $subjectId
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getSubject(int $subjectId)
    {
        $subjects = new TeachingSubjectController($this->institution);

        return $subjects->index()->filter(function ($subject) use ($subjectId) {
            return $subject->id === $subjectId;
        })->first();
    }

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

    private function getTimetableStructure(): Collection
    {
        $key = $this->institution->getConfigName() . 'timetableStructure.index';

        return Cache::remember($key, now()->addWeek(), function () {
            $schedule = new TimetableStructureController($this->institution);

            return $schedule->index();
        });
    }
}
