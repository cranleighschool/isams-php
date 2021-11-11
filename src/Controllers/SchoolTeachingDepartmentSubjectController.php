<?php

namespace spkm\isams\Controllers;

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
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/school/departments/teaching';
    }

    /**
     * Show the resource.
     *
     * @param  int  $departmentId
     * @return Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(int $departmentId)
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $departmentId . '/subjects', ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return collect($decoded->subjects)->map(function ($item) {
            return new TeachingSubject($item);
        });
    }
}
