<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\EmployeeRole;

class HumanResourcesEmployeeRoleController extends Endpoint
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
    public function store(int $id, int $roleId): JsonResponse
    {
        $response = $this->guzzle->request('POST', $this->endpoint.'/'.$id.'/roles/'.$roleId, [
            'headers' => $this->getHeaders(),
        ]);

        return $this->response(201, $response, 'The employee has been associated with the specified role.');
    }

    /**
     * Show the specified resource.
     *
     *
     * @throws GuzzleException
     */
    public function show(int $id): EmployeeRole
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id.'/roles', ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new EmployeeRole($decoded);
    }
}
