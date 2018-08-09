<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Language;
use spkm\isams\Contracts\Institution;
use Illuminate\Support\Facades\Cache;

class LanguageController extends Endpoint
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
        $this->endpoint = $this->getDomain().'/api/systemconfiguration/list/languages';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        $key = $this->institution->getConfigName().'languages.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, 10080, function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'items', Language::class);
        });
    }

    /**
     * Create a new resource.
     *
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(array $attributes)
    {
        $this->validate(['name'], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201,$response,'The language has been created.');
    }

    /**
     * Update the specified resource.
     *
     * @param int $id
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(int $id, array $attributes)
    {
        $this->validate(['name'], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201,$response,'The language has been updated.');
    }

    /**
     * Remove the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function destroy(int $id)
    {
        $response = $this->guzzle->request('DELETE', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
        ]);


        return $this->response(200,$response,'The language has been removed.');
    }
}
