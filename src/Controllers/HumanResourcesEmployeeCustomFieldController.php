<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Employee;
use spkm\isams\Wrappers\EmployeeCustomField;
use spkm\isams\Wrappers\EmployeeCustomFieldProperty;

class HumanResourcesEmployeeCustomFieldController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain() . '/api/humanresources/employees';
    }

    /**
     * Retrieve all custom fields associated with an employee.
     *
     * @param  int  $id
     * @return Collection
     *
     * @throws GuzzleException
     */
    public function index(int $id): Collection
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id . '/customFields', ['headers' => $this->getHeaders()]);

        return $this->wrapJson($response->getBody()->getContents(), 'customFields', EmployeeCustomFieldProperty::class);
    }

    /**
     * Retrieve a custom field associated with an employee.
     *
     * @param  int  $id
     * @param  int  $customFieldId
     * @return EmployeeCustomField
     *
     * @throws GuzzleException
     */
    public function show(int $id, int $customFieldId): EmployeeCustomField
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id . '/customFields/' . $customFieldId, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new EmployeeCustomField($decoded->customFields[0]);
    }

    /**
     * Update the specified resource.
     *
     * @param  int  $id
     * @param  int  $customFieldId
     * @param  array  $attributes
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function update(int $id, int $customFieldId, array $attributes): JsonResponse
    {
        $this->validate([
            'value',
        ], $attributes);

        $response = $this->guzzle->request('PATCH', $this->endpoint . '/' . $id . '/customFields/' . $customFieldId, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The employee custom field has been updated.');
    }

    /**
     * Update the specified resource.
     *
     * @param  int  $id
     * @param  array  $attributes  an array with one or more arrays within
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function bulkUpdate(int $id, array $attributes): JsonResponse
    {
        foreach ($attributes as $attributeSubArray) {
            $this->validate([
                'id',
                'value',
            ], $attributeSubArray);
        }

        $response = $this->guzzle->request('PATCH', $this->endpoint . '/' . $id . '/customFields', [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The employee custom field has been updated.');
    }
}
