<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\EstateBuilding;

class EstateBuildingController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain() . '/api/estates/buildings';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Collection
     *
     * @throws GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName() . 'estateBuildings.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'buildings', EstateBuilding::class);
        });
    }

    /**
     * Create a new resource.
     *
     * @param  array  $attributes
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function store(array $attributes): JsonResponse
    {
        $this->validate(['name', 'initials'], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The building has been created.');
    }

    /**
     * Update the specified resource.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function update(int $id, array $attributes): JsonResponse
    {
        $this->validate(['name'], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint . '/' . $id, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The building has been updated.');
    }
}
