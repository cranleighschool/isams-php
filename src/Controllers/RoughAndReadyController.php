<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;

/**
 * Class RoughAndReadyController.
 */
class RoughAndReadyController extends Endpoint
{
    /**
     * @param  string  $method
     * @param  string  $endpoint
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $method, string $endpoint, array $query = [])
    {
        $endpoint = $this->endpoint . $endpoint;
        $response = $this->guzzle->request($method, $endpoint, ['query' => $query, 'headers' => $this->getHeaders()]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/';
    }
}
