<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use spkm\isams\Endpoint;

class RegistrationAbsenceCodeController extends Endpoint
{
    /**
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain() . '/api/registration/absencecodes';
    }

    /**
     * @return Collection
     * @throws GuzzleException
     */
    public function index(): Collection
    {
        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        $absenceCodes = json_decode($response->getBody()->getContents())->absenceCodes;

        return collect($absenceCodes)->pluck('name');
    }
}
