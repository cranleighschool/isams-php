<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\Contracts\Institution;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Wrappers\Pupil;

class CurrentPupilController extends Endpoint
{
    /**
     * @var \spkm\isams\Contracts\Institution
     */
    private $institution;

    /**
     * @var string
     */
    protected $endpoint;

    public function __construct(Institution $institution)
    {
        $this->institution = $institution;
        $this->setGuzzle();
        $this->setEndpoint();
    }

    /**
     * Get the School to be queried
     *
     * @return \spkm\Isams\Contracts\Institution
     */
    protected function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set the URL the request is made to
     *
     * @return void
     * @throws \Exception
     */
    private function setEndpoint()
    {
        $this->endpoint = $this->getDomain().'/api/students';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        $key = $this->institution->getConfigName().'currentPupils.index';

        $decoded = json_decode($this->pageRequest($this->endpoint, 1));
        $items = collect($decoded->students)->map(function ($item) {
            return new Pupil($item, $this->institution);
        });

        $totalCount = $decoded->totalCount;
        $pageNumber = $decoded->page + 1;
        while ($pageNumber <= $decoded->totalPages):
            $decoded = json_decode($this->pageRequest($this->endpoint, $pageNumber));

            collect($decoded->students)->map(function ($item) use ($items) {
                $items->push(new Pupil($item, $this->institution));
            });

            $pageNumber++;
        endwhile;

        if ($totalCount !== $items->count()):
            throw new \Exception($items->count().' items were returned instead of '.$totalCount.' as specified on page 1.');
        endif;

        return Cache::remember($key, 10080, function () use ($items) {
            return $items;
        });
    }

    /**
     * Create a new resource.
     *
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(array $attributes)
    {
        $this->validate([
            'forename',
            'surname',
            'dob',
            'yearGroup'
        ], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The pupil has been created.');
    }

    /**
     * Show the specified resource
     *
     * @param string $schoolId
     * @return \spkm\isams\Wrappers\Pupil
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(string $schoolId)
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$schoolId, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new Pupil($decoded, $this->institution);
    }

    /**
     * Update the specified resource.
     *
     * @param string $schoolId
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(string $schoolId, array $attributes)
    {
        $this->validate([
            'forename',
            'surname',
            'dob',
            'yearGroup'
        ], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint.'/'.$schoolId, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The pupil has been updated.');
    }
}
