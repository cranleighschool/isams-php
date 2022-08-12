<?php

namespace spkm\isams;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\TeachingSubjectController;
use spkm\isams\Controllers\TimetableStructureController;
use spkm\isams\Wrappers\Lesson;
use spkm\isams\Wrappers\TeachingSubject;
use spkm\isams\Wrappers\TimetableDay;

trait TimetableControllerTrait
{
    /**
     * @param  string  $schoolId
     * @return Collection
     *
     * @throws GuzzleException
     */
    public function getWeekCalendar(string $schoolId): Collection
    {
        $timetable = $this->show($schoolId);
        $result = [];
        foreach ($this->getTimetableStructure() as $day => $days) {
            foreach ($days as $period) {
                $lesson = collect($timetable['sets'])->filter(function ($item) use ($period) {
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
     * Get the timetable for the specified pupil.
     *
     * @param  string  $schoolId
     * @return Collection
     *
     * @throws GuzzleException
     */
    public function show(string $schoolId): Collection
    {
        $this->endpoint = $this->endpoint . '/' . $schoolId;
        $response = $this->guzzle->request(
            'GET',
            $this->endpoint,
            ['headers' => $this->getHeaders()]
        );

        $decoded = json_decode($response->getBody()->getContents());

        return collect($decoded);
    }

    /**
     * @return Collection
     */
    private function getTimetableStructure(): Collection
    {
        $key = $this->institution->getConfigName() . 'timetableStructure.index';

        return Cache::remember($key, now()->addWeek(), function () {
            $schedule = new TimetableStructureController($this->institution);

            return $schedule->index();
        });
    }

    /**
     * @param  int  $subjectId
     * @return TeachingSubject
     *
     * @throws GuzzleException
     */
    private function getSubject(int $subjectId): TeachingSubject
    {
        $subjects = new TeachingSubjectController($this->institution);

        return $subjects->index()->filter(function ($subject) use ($subjectId) {
            return $subject->id === $subjectId;
        })->first();
    }
}
