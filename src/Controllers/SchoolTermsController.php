<?php

namespace spkm\isams\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Exceptions\FailedtoFindNearestTerm;
use spkm\isams\Wrappers\SchoolTerm;

class SchoolTermsController extends Endpoint
{
    /**
     * @var null
     */
    private $terms = null;

    /**
     * Set the URL the request is made to.
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/school/terms';
    }

    /**
     * Returns a Term, for the given Institution based on
     * the current date (albeit cached for a week).
     *
     * @return object
     */
    public function thisTerm(): object
    {
        Cache::forget('termDatesThisTerm_' . $this->institution->short_code);

        return Cache::remember('termDatesThisTerm_' . $this->institution->short_code, now()->addWeek(), function () {
            $currentTerm = $this->getCurrentTerm();
            array_walk($currentTerm, function (&$item, $key) {
                if ($key == 'startDate' || $key == 'finishDate') {
                    $item = Carbon::parse($item);
                }
            });

            return $currentTerm;
        });
    }

    /**
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        if ($this->terms !== null) {
            return $this->terms;
        }
        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);
        $terms = collect(json_decode($response->getBody()->getContents())->terms);
        $terms->each(function ($item) {
            $item->school = $this->institution->only(['school_id', 'short_code', 'long_name']);
        });

        $this->terms = $terms->mapInto(SchoolTerm::class);

        return $this->terms;
    }

    /**
     * @return object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCurrentTerm(): SchoolTerm
    {
        $terms = $this->index();

        $findTerm = $terms->filter(function ($item) {
            $startDate = $item->startDate;
            $finishDate = $item->finishDate;
            if (Carbon::now()->between(Carbon::parse($startDate), Carbon::parse($finishDate))) {
                return $item;
            }
        });

        if ($findTerm->count() === 1) {
            return $findTerm->first();
        } else {
            return $this->getNearestTerm();
        }
    }

    /**
     * @param  int  $year
     *
     * @return Collection
     */
    public function getYear(int $year): Collection
    {
        $terms = $this->index();

        return $terms->filter(function ($item) use ($year) {
            if ($item->schoolYear === $year) {
                return $item;
            }
        });
    }

    /**
     * @return object
     * @throws \App\Exceptions\FailedToFindNearestTerm
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getNearestTerm(): SchoolTerm
    {
        $terms = $this->index();

        $nearestTerm = $terms->filter(function ($item) {
            if (Carbon::parse($item->finishDate) > now()->subMonths(2) && Carbon::parse($item->startDate) < now()->addMonths(2)) {
                return $item;
            }
        });

        if ($nearestTerm->count() === 1) {
            return $nearestTerm->first();
        } elseif ($nearestTerm->count() === 2) {
            /**
             * If there are exactly two nearest terms at this point, then we assume it's a holiday.
             * We now want to find the nearest term relevant to today's date.
             */
            $term['first'] = $nearestTerm->first();
            $term['last'] = $nearestTerm->last();

            $daysSinceEndOfTerm = now()->diffInMilliseconds($term['first']->finishDate);
            $daysUntilStartOfTerm = now()->diffInMilliseconds($term['last']->startDate);

            $times = collect([
                'first' => $daysSinceEndOfTerm,
                'last' => $daysUntilStartOfTerm,
            ])->sort()->keys();

            // The Nearest Term...
            return $term[$times[0]];
        } else {
            throw new FailedtoFindNearestTerm('Could not find nearest term', 500);
        }
    }
}
