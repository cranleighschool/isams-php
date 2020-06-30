<?php

namespace spkm\isams\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Pupil;

class CurrentPupilController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/students';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName() . 'currentPupils.index';

        $decoded = json_decode($this->pageRequest($this->endpoint, 1));
        $items = collect($decoded->students)->map(function ($item) {
            return new Pupil($item);
        });

        $totalCount = $decoded->totalCount;
        $pageNumber = $decoded->page + 1;
        while ($pageNumber <= $decoded->totalPages):
            $decoded = json_decode($this->pageRequest($this->endpoint, $pageNumber));

        collect($decoded->students)->map(function ($item) use ($items) {
            $items->push(new Pupil($item));
        });

        $pageNumber++;
        endwhile;

        if ($totalCount !== $items->count()):
            throw new \Exception($items->count() . ' items were returned instead of ' . $totalCount . ' as specified on page 1.');
        endif;

        return Cache::remember($key, config('isams.cacheDuration'), function () use ($items) {
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
    public function store(array $attributes): JsonResponse
    {
        $this->validate([
            'forename',
            'surname',
            'dob',
            'yearGroup',
        ], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The pupil has been created.');
    }

    /**
     * Show the specified resource.
     *
     * @param string $schoolId
     * @return \spkm\isams\Wrappers\Pupil
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(string $schoolId): Pupil
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $schoolId, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new Pupil($decoded);
    }

    /**
     * Update the specified resource.
     *
     * @param string $schoolId
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(string $schoolId, array $attributes): JsonResponse
    {
        $this->validate([
            'forename',
            'surname',
            'dob',
            'yearGroup',
        ], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint . '/' . $schoolId, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The pupil has been updated.');
    }
}
