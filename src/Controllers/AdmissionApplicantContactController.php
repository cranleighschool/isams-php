<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\PupilContact;

/**
 * IMPORTANT NOTE:.
 *
 * As of July 2018, iSAMS have notified us that the contacts API endpoints are temporary & will be changed with the rollout of
 * the upgraded pupil contact module in 2019/2020.
 *
 * Update: Nov 2021 & there is still no sign of the upgraded pupil contact module in the near future.
 */
class AdmissionApplicantContactController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain() . '/api/admissions/applicants';
    }

    /**
     * Create a new resource.
     *
     * @param  string  $schoolId
     * @param  array  $attributes
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function store(string $schoolId, array $attributes): JsonResponse
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

        $this->endpoint = $this->endpoint . '/' . $schoolId . '/tempcontacts';

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(Response::HTTP_CREATED, $response, 'The contact has been created.');
    }

    /**
     * Get all contacts for the specified applicant.
     *
     * @param  string  $schoolId
     * @return Collection
     * @throws GuzzleException
     */
    public function show(string $schoolId): Collection
    {
        $this->endpoint = $this->endpoint . '/' . $schoolId . '/tempcontacts';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        $contacts = collect($decoded->contacts)->map(function ($item) {
            return new PupilContact($item);
        });

        return $contacts;
    }

    /**
     * Gets a specific applicant contact.
     *
     * @param  string  $schoolId
     * @param  int  $contactId
     * @return PupilContact
     * @throws GuzzleException
     */
    public function showContact(string $schoolId, int $contactId): PupilContact
    {
        $this->endpoint = $this->endpoint . '/' . $schoolId . '/tempcontacts/' . $contactId;

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new PupilContact($decoded);
    }

    /**
     * Update the specified resource.
     *
     * @param  string  $schoolId
     * @param  int  $contactId
     * @param  array  $attributes
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function update(string $schoolId, int $contactId, array $attributes): JsonResponse
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

        $this->endpoint = $this->endpoint . '/' . $schoolId . '/tempcontacts/' . $contactId;

        $response = $this->guzzle->request('PUT', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The contact has been updated.');
    }
}
