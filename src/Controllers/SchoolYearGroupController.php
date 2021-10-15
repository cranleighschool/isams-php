<?php

namespace spkm\isams\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\YearGroup;

class SchoolYearGroupController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain() . '/api/school/yeargroups';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName() . 'schoolYeargroups.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'yearGroups', YearGroup::class);
        });
    }

    /**
     * Show the resource.
     *
     * @param  int  $id
     * @return \spkm\isams\Wrappers\YearGroup
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(int $id): YearGroup
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new YearGroup($decoded);
    }
}
