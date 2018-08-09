<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\Wrappers\TeachingSubject;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Contracts\Institution;

class TeachingSubjectController extends Endpoint
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
        $this->endpoint = $this->getDomain().'/api/teaching/subjects';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        $key = $this->institution->getConfigName().'teachingSubjects.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, 10080, function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'subjects', TeachingSubject::class);
        });
    }

    /**
     * Create a new resource.
     */
    public function store()
    {
        //TODO
    }

    /**
     * Update the specified resource.
     */
    public function update()
    {
        //TODO
    }

    /**
     * Show the specified resource
     *
     * @param int $id
     * @return \spkm\isams\Wrappers\TeachingSubject
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(int $id)
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new TeachingSubject($decoded, $this->institution);
    }
}
