<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\EmployeeQualification;

class HumanResourcesEmployeeQualificationController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/humanresources/employees';
    }

    /**
     * Create a new resource.
     *
     *
     * @throws GuzzleException
     */
    public function store(int $id, array $attributes): JsonResponse
    {
        $this->validate([
            'dateAwarded',
            'name',
        ], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint.'/'.$id.'/qualifications', [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The employee qualification has been created.');
    }

    /**
     * Show the specified resource.
     *
     *
     * @throws GuzzleException
     */
    public function show(int $id): EmployeeQualification
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id.'/qualifications', ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new EmployeeQualification($decoded);
    }
}
