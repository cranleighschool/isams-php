<?php

namespace spkm\isams\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Applicant;

class AdmissionApplicantController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain() . '/api/admissions/applicants';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName() . 'admissionApplicants.index';

        $decoded = json_decode($this->pageRequest($this->endpoint, 1));
        $items = collect($decoded->applicants)->map(function ($item) {
            return new Applicant($item);
        });

        $totalCount = $decoded->totalCount;
        $pageNumber = $decoded->page + 1;
        while ($pageNumber <= $decoded->totalPages) {
            $decoded = json_decode($this->pageRequest($this->endpoint, $pageNumber));

            collect($decoded->applicants)->map(function ($item) use ($items) {
                $items->push(new Applicant($item));
            });

            $pageNumber++;
        }

        if ($totalCount !== $items->count()) {
            throw new \Exception($items->count() . ' items were returned instead of ' . $totalCount . ' as specified on page 1.');
        }

        return Cache::remember($key, $this->getCacheDuration(), function () use ($items) {
            return $items;
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
        $this->validate([
            'forename',
            'surname',
            'preferredName',
            'admissionStatus',
            'boardingStatus',
            'gender',
        ], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The applicant has been created.');
    }

    /**
     * Show the specified resource.
     *
     * @param string $schoolId
     * @return \spkm\isams\Wrappers\Applicant
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(string $schoolId): Applicant
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $schoolId, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new Applicant($decoded);
    }

    /**
     * Update the specified resource.
     *
     * @param string $schoolId
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(string $schoolId, array $attributes): JsonResponse
    {
        $this->validate([
            'forename',
            'surname',
            'preferredName',
            'admissionStatus',
            'boardingStatus',
            'gender',
        ], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint . '/' . $schoolId, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The applicant has been updated.');
    }
}
