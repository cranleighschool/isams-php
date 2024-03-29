<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\TeachingSubject;

class SchoolTeachingDepartmentSubjectController extends Endpoint
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
        $this->endpoint = $this->getDomain() . '/api/school/departments/teaching';
    }

    /**
     * Show the resource.
     *
     * @param  int  $departmentId
     * @return Collection
     *
     * @throws GuzzleException
     */
    public function show(int $departmentId): Collection
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $departmentId . '/subjects', ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return collect($decoded->subjects)->map(function ($item) {
            return new TeachingSubject($item);
        });
    }
}
