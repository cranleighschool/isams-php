<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Pupil;

class PupilController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/students';
    }

    /**
     * Display a listing of the resource.
     *
     *
     * @throws GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName().'currentPupils.index';

        $decoded = json_decode($this->pageRequest($this->endpoint, 1));
        $items = collect($decoded->students)->map(function ($item) {
            return new Pupil($item);
        });

        $totalCount = $decoded->totalCount;
        $pageNumber = $decoded->page + 1;
        while ($pageNumber <= $decoded->totalPages) {
            $decoded = json_decode($this->pageRequest($this->endpoint, $pageNumber));

            collect($decoded->students)->map(function ($item) use ($items) {
                $items->push(new Pupil($item));
            });

            $pageNumber++;
        }

        if ($totalCount !== $items->count()) {
            throw new Exception($items->count().' items were returned instead of '.$totalCount.' as specified on page 1.');
        }

        return Cache::remember($key, $this->getCacheDuration(), function () use ($items) {
            return $items;
        });
    }

    /**
     * Create a new resource.
     *
     *
     * @throws GuzzleException
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
     *
     * @throws GuzzleException
     */
    public function show(string $schoolId): Pupil
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$schoolId, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new Pupil($decoded);
    }

    /**
     * Update the specified resource.
     *
     *
     * @throws GuzzleException
     */
    public function update(string $schoolId, array $attributes): JsonResponse
    {
        $this->validate([
            'forename',
            'surname',
            'dob',
            'yearGroup',
        ], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint.'/'.$schoolId, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The pupil has been updated.');
    }
}
