<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Wrappers\House;

class SchoolHouseController extends Endpoint
{
    /**
     * Set the URL the request is made to
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/school/houses';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName().'schoolHouses.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, config('isams.cacheDuration'), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'houses', House::class);
        });
    }

    /**
     * Show the resource
     *
     * @param int $id
     * @return \spkm\isams\Wrappers\House
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(int $id): House
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new House($decoded, $this->institution);
    }
}
