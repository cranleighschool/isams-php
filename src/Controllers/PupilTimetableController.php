<?php

namespace spkm\isams\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Lesson;
use spkm\isams\Wrappers\SchoolTerm;
use spkm\isams\Wrappers\TimetableDay;

/**
 * Class PupilTimetableController.
 */
class PupilTimetableController extends Endpoint
{
    /**
     * @var \Carbon\Carbon
     */
    public $termStart;
    /**
     * @var \Carbon\Carbon
     */
    public $termEnd;

    /**
     * @param  string  $schoolId
     * @return \Illuminate\Support\Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @return \Illuminate\Support\Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(string $schoolId): Collection
    {
        $this->endpoint = $this->endpoint . '/' . $schoolId;
        $response = $this->guzzle->request('GET', $this->endpoint,
            ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return collect($decoded);
    }

    /**
     * @return \Illuminate\Support\Collection
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
     * @return mixed
     *
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
     * @return \spkm\isams\Wrappers\SchoolTerm
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCurrentTermDates(): SchoolTerm
    {
        $terms = new SchoolTermsController($this->institution);
        $currentTerm = $terms->getCurrentTerm();

        $this->termStart = $currentTerm->startDate;
        $this->termEnd = $currentTerm->finishDate;

        return $currentTerm;
    }

    /**
     * Set the URL the request is made to.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/timetables/students';
    }
}
