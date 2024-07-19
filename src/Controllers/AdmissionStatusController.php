<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\AdmissionStatus;

class AdmissionStatusController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/admissions/statuses';
    }

    /**
     * Display a listing of the resource.
     *
     *
     * @throws GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName().'admissionsStatuses.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'items', AdmissionStatus::class);
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
        $this->validate(['name'], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The admission status has been created.');
    }

    /**
     * Update the specified resource.
     *
     *
     * @throws GuzzleException
     */
    public function update(int $id, array $attributes): JsonResponse
    {
        $this->validate(['name'], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The admission status has been updated.');
    }

    /**
     * Remove the specified resource.
     *
     *
     * @throws GuzzleException
     */
    public function destroy(int $id): JsonResponse
    {
        $response = $this->guzzle->request('DELETE', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
        ]);

        return $this->response(200, $response, 'The admission status has been removed.');
    }
}
