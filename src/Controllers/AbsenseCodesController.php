<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;

/**
 * Class AbsenseCodesController
 * @package spkm\isams\Controllers
 */
class AbsenseCodesController extends Endpoint
{
    /**
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        $absenceCodes = json_decode($response->getBody()->getContents())->absenceCodes;

        return collect($absenceCodes)->pluck('name');
    }

    /**
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain().'/api/registration/absencecodes';
    }
}
