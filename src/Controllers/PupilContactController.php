<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\Contracts\Institution;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Wrappers\PupilContact;

/**
 * IMPORTANT NOTE:
 *
 * As of July 2018, iSAMS have notified us that the contacts API endpoints are temporary & will be changed with the rollout of
 * the upgraded pupil contact module in 2019/2020
 */
class PupilContactController extends Endpoint
{
    /**
     * @var \spkm\isams\Contracts\Institution
     */
    private $institution;

    /**
     * @var string
     */
    protected $endpoint;

    public function __construct(Institution $institution)
    {
        $this->institution = $institution;
        $this->setGuzzle();
        $this->setEndpoint();
    }

    /**
     * Get the School to be queried
     *
     * @return \spkm\Isams\Contracts\Institution
     */
    protected function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set the URL the request is made to
     *
     * @return void
     * @throws \Exception
     */
    private function setEndpoint()
    {
        $this->endpoint = $this->getDomain().'/api/students';

        //Docs state that the applicant contact route is '/api/admissions/applicants/{schoolId}/tempcontacts'
        //However '/api/students/{schoolId}/tempcontacts' seems to work for applicants in addition to current pupils
    }

    /**
     * !! DEPRECATED ENDPOINT !!
     *
     * Create a new resource.
     *
     * @param string $schoolId
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(string $schoolId, array $attributes)
    {
        $this->validate([
            'relationship',
            'title',
            'forename',
            'surname',
            'address1',
            'postcode',
            'country',
        ], $attributes);

        $this->endpoint = $this->endpoint.'/'.$schoolId.'/tempcontacts';

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The contact has been created.');
    }

    /**
     * Show the specified resource
     * Note: Gets all contacts for the specified applicant/current pupil
     *
     * @param string $schoolId
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(string $schoolId)
    {
        $this->endpoint = $this->endpoint.'/'.$schoolId.'/contacts';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        $contacts = collect($decoded->contacts)->map(function ($item) {
            return new PupilContact($item, $this->institution);
        });

        return $contacts;
    }

    /**
     * !! DEPRECATED ENDPOINT !!
     *
     * Show the specified resource
     * Note: Gets a specific student contact.
     *
     * @param string $schoolId
     * @param int $contactId
     * @return \spkm\isams\Wrappers\PupilContact
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function showContact(string $schoolId, int $contactId)
    {
        $this->endpoint = $this->endpoint.'/'.$schoolId.'/tempcontacts/'.$contactId;

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new PupilContact($decoded, $this->institution);
    }

    /**
     * !! DEPRECATED ENDPOINT !!
     *
     * Update the specified resource.
     *
     * @param string $schoolId
     * @param int $contactId
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(string $schoolId, int $contactId, array $attributes)
    {
        $this->validate([
            'relationship',
            'title',
            'forename',
            'surname',
            'address1',
            'postcode',
            'country',
        ], $attributes);

        $this->endpoint = $this->endpoint.'/'.$schoolId.'/tempcontacts/'.$contactId;

        $response = $this->guzzle->request('PUT', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The contact has been updated.');
    }
}
