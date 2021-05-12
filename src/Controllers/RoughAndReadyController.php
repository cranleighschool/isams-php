<?php


namespace spkm\isams\Controllers;

use Psr\Http\Message\ResponseInterface;
use spkm\isams\Endpoint;

/**
 * Class RoughAndReadyController
 * @package spkm\isams\Controllers
 */
class RoughAndReadyController extends Endpoint
{
    /**
     * @param  string  $method
     * @param  string  $endpoint
     * @param string $query
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $method, string $endpoint, array $query=[])
    {
        $endpoint = $this->endpoint . $endpoint;
        $response = $this->guzzle->request($method, $endpoint, ['query' => $query, 'headers' => $this->getHeaders()]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param  string  $endpoint
     * @param  array  $query
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $endpoint, array $query=[])
    {
        return $this->request('GET', $endpoint, $query);
    }

    /**
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/';
    }
}
