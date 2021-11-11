<?php

namespace spkm\isams\Logic;

use Illuminate\Support\Collection;

/*
 * Convert the API output from PupilTimetableController show method into
 * sometime we can use to build a html table.
 *
 * Example blade table using output from $this->handle()
 *         <table>
            <thead>
            <tr>
                @foreach($data['days'] as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($data['dataset'] as $slot)
                <tr>
                    @foreach($slot as $period)
                        <td>
                            <dl>
                                <dd>{{ $period->name }}</dd>
                                <dd><strong>{{ optional($period->lesson)->subjectName }}</strong></dd>
                                <dd>{{ $period->startTime }}</dd>
                                <dd>{{ $period->endTime }}</dd>
                            </dl>
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
 */
class PupilTimetableCompiler
{
    /*
     * The pupil timetable as output from PupilTimetableController show()
     *
     * @var Collection
     */
    protected $timetable;

    /*
     * Names of the weekdays used by the timetable
     *
     * @var array
     */
    protected $days;

    /*
     * The maximum number of periods in (any) single day
     *
     * @var int
     */
    protected $maxDailyPeriods;

    /*
     * Instantiate the class
     */
    public function __construct(Collection $timetable)
    {
        $this->timetable = $timetable;
    }

    /*
     * Handle the task
     */
    public function handle(): array
    {
        $this->setDefinition();

        return [
            'days' => $this->days,
            'dataset' => $this->createDataset(),
        ];
    }

    /*
     * Recreate the variables to build a html table
     */
    private function createDataset(): array
    {
        $i = 0;
        $periodNumber = 1;
        $dataset = [];
        while ($i < $this->maxDailyPeriods) {
            //1, 2, ...
            $row = null;
            foreach ($this->days as $day) {
                //Monday, Tuesday, ...
                $row[] = ($this->timetable[$day]->toArray()[$i]);
            }

            $dataset['Period_' . $periodNumber] = $row;
            $periodNumber++;
            $i++;
        }

        return $dataset;
    }

    /*
     * Set the timetable definition
     */
    private function setDefinition(): void
    {
        $this->maxDailyPeriods = 0;

        $this->days = array_keys($this->timetable->toArray());
        foreach ($this->timetable as $day) {
            $count = count($day->toArray());

            if ($count > $this->maxDailyPeriods) {
                $this->maxDailyPeriods = $count;
            }
        }
    }
}
