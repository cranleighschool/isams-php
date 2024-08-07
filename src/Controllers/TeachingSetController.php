<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\TeachingSet;
use spkm\isams\Wrappers\TeachingSetList;

class TeachingSetController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/teaching/sets';
    }

    /**
     * Display a listing of the resource.
     *
     *
     * @throws GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName().'teachingSets.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'sets', TeachingSet::class);
        });
    }

    /**
     * Show the specified resource.
     *
     *
     * @throws GuzzleException
     */
    public function show(int $id): TeachingSetList
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id.'/setList', ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new TeachingSetList($decoded);
    }
}
