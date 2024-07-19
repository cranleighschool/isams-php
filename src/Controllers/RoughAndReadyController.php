<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use spkm\isams\Endpoint;

class RoughAndReadyController extends Endpoint
{
    /**
     * @return mixed
     *
     * @throws GuzzleException
     */
    public function request(string $method, string $endpoint, array $query = [], array $json = [])
    {
        $endpoint = $this->endpoint.$endpoint;
        $response = $this->guzzle->request($method, $endpoint, ['query' => $query, 'headers' => $this->getHeaders(), 'json' => $json]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @return mixed
     *
     * @throws GuzzleException
     */
    public function get(string $endpoint, array $query = [])
    {
        return $this->request('GET', $endpoint, $query);
    }

    /**
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/';
    }
}
