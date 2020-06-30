<?php

namespace spkm\isams\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\School;

class OtherSchoolController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/otherschools';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName() . 'otherSchools.index';

        $decoded = json_decode($this->pageRequest($this->endpoint, 1));
        $items = collect($decoded->otherSchools)->map(function ($item) {
            return new School($item);
        });

        $totalCount = $decoded->totalCount;
        $pageNumber = $decoded->page + 1;
        while ($pageNumber <= $decoded->totalPages):
            $decoded = json_decode($this->pageRequest($this->endpoint, $pageNumber));

        collect($decoded->otherSchools)->map(function ($item) use ($items) {
            $items->push(new School($item));
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
            'schoolName',
            'schoolCode',
            'schoolTelephone',
            'postcode',
        ], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The school has been created.');
    }

    /**
     * Show the specified resource.
     *
     * @param int $id
     * @return \spkm\isams\Wrappers\School
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(int $id): School
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new School($decoded);
    }

    /**
     * Update the specified resource.
     *
     * @param int $id
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(int $id, array $attributes): JsonResponse
    {
        $this->validate([
            'schoolName',
            'schoolCode',
            'schoolTelephone',
            'postcode',
        ], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint . '/' . $id, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The school has been updated.');
    }
}
