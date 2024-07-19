<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\EmployeeDepartment;

class HumanResourcesEmployeeDepartmentController extends Endpoint
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
     * Show the specified resource.
     *
     *
     * @throws GuzzleException
     */
    public function show(int $id): Collection
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id.'/departments', ['headers' => $this->getHeaders()]);

        return $this->wrapJson($response->getBody()->getContents(), 'departments', EmployeeDepartment::class);
    }
}
