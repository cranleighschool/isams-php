<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Title;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TitleController extends Endpoint
{
    /**
     * Set the URL the request is made to
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/systemconfiguration/list/titles';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName().'titles.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, 10080, function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'items', Title::class);
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

        return $this->response(201, $response, 'The title has been created.');
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

        return $this->response(200, $response, 'The title has been updated.');
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

        return $this->response(200, $response, 'The title has been removed.');
    }
}
