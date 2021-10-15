<?php

namespace spkm\isams\Controllers;

use Illuminate\Support\Collection;
use spkm\isams\Endpoint;

/**
 * Class AbsenseCodesController.
 */
class AbsenseCodesController extends Endpoint
{
    /**
     * @return \Illuminate\Support\Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        $absenceCodes = json_decode($response->getBody()->getContents())->absenceCodes;

        return collect($absenceCodes)->pluck('name');
    }

    /**
     * @throws \Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain() . '/api/registration/absencecodes';
    }
}
