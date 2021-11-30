<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\TeachingSubject;

class TeachingSubjectController extends Endpoint
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
        $this->endpoint = $this->getDomain() . '/api/teaching/subjects';
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
        $key = $this->institution->getConfigName() . 'teachingSubjects.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'subjects', TeachingSubject::class);
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
        $this->validate([
            'active',
            'code',
            'formSubject',
            'name',
            'reportingName',
            'setSubject',
        ], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The subject has been created.');
    }

    /**
     * Update the specified resource.
     *
     * @param  string  $subjectId
     * @param  array  $attributes
     * @return void
     *
     * @throws GuzzleException
     */
    public function update(string $subjectId, array $attributes): JsonResponse
    {
        $this->validate([
            'active',
            'code',
            'formSubject',
            'name',
            'reportingName',
            'setSubject',
        ], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint . '/' . $subjectId, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The subject has been updated.');
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return TeachingSubject
     *
     * @throws GuzzleException
     */
    public function show(int $id): TeachingSubject
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new TeachingSubject($decoded);
    }
}
