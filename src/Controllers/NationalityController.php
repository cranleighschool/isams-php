<?php

namespace spkm\isams\Controllers;

use Illuminate\Support\Collection;
use spkm\isams\Endpoint;
use Illuminate\Http\JsonResponse;
use spkm\isams\Wrappers\Nationality;
use Illuminate\Support\Facades\Cache;

class NationalityController extends Endpoint
{
    /**
     * Set the URL the request is made to
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain().'/api/systemconfiguration/list/nationalities';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName().'nationalities.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, config('isams.cacheDuration'), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'items', Nationality::class);
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
        $this->validate(['name'], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201,$response,'The nationality has been created.');
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
        $this->validate(['name'], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200,$response,'The nationality has been updated.');
    }

    /**
     * Remove the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function destroy(int $id): JsonResponse
    {
        $response = $this->guzzle->request('DELETE', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
        ]);

        return $this->response(200,$response,'The nationality has been removed.');
    }
}
