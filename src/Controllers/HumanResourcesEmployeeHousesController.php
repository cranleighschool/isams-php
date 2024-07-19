<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\Wrappers\EmployeeHouse;

class HumanResourcesEmployeeHousesController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     *
     * @throws \Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/humanresources/employees';
    }

    /**
     * Show the specified resource.
     *
     * @return \spkm\isams\Wrappers\Employee
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(int $id)
    {
        $response = $this
            ->guzzle
            ->request(
                'GET',
                $this->endpoint.'/'.$id.'/houses',
                [
                    'headers' => $this->getHeaders(),
                ]
            );

        $decoded = json_decode($response->getBody()->getContents());

        return new EmployeeHouse($decoded, $this->institution);
    }
}
