<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\Contracts\Institution;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Wrappers\YearGroup;

class SchoolYearGroupController extends Endpoint
{
    /**
     * @var \spkm\isams\Contracts\Institution
     */
    protected $institution;

    /**
     * @var string
     */
    protected $endpoint;

    public function __construct(Institution $institution)
    {
        $this->institution = $institution;
        $this->setGuzzle();
        $this->setEndpoint();
    }

    /**
     * Get the School to be queried
     *
     * @return \spkm\Isams\Contracts\Institution
     */
    protected function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set the URL the request is made to
     *
     * @return void
     * @throws \Exception
     */
    private function setEndpoint()
    {
        $this->endpoint = $this->getDomain().'/api/school/yeargroups';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        $key = $this->institution->getConfigName().'schoolYeargroups.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, 10080, function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'yearGroups', YearGroup::class);
        });
    }

    /**
     * Show the resource
     *
     * @param int $id
     * @return \spkm\isams\Wrappers\YearGroup
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(int $id)
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new YearGroup($decoded, $this->institution);
    }
}
