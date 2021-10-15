<?php

namespace spkm\isams;

use GuzzleHttp\Client as Guzzle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use spkm\isams\Contracts\Institution;
use spkm\isams\Exceptions\ValidationException;

abstract class Endpoint
{
    /**
     * @var Guzzle
     */
    protected $guzzle;

    /**
     * @var \spkm\isams\Contracts\Institution
     */
    protected $institution;

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
     * Instantiate Guzzle.
     *
     * @return void
     */
    protected function setGuzzle()
    {
        $this->guzzle = new Guzzle();
    }

    /**
     * Set the URL the request is made to.
     *
     * @return void
     */
    abstract protected function setEndpoint(): void;

    /**
     * Get a specific page from the api.
     *
     * @param  string  $url
     * @param  int  $page
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pageRequest(string $url, int $page)
    {
        $response = $this->guzzle->request('GET', $url, [
            'query' => ['page' => $page],
            'headers' => $this->getHeaders(),
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * Get the Guzzle headers for a request.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Get an access token for the specified Institution.
     *
     * @return string
     */
    protected function getAccessToken(): string
    {
        return (new Authentication($this->getInstitution()))->getToken();
    }

    /**
     * Wrap the json returned by the API.
     *
     * @param  string  $json
     * @param  string  $property
     * @param  string  $wrapper
     *
     * @return \Illuminate\Support\Collection
     */
    public function wrapJson(string $json, string $property, string $wrapper): Collection
    {
        $decoded = json_decode($json);

        return collect($decoded->$property)->map(function ($item) use ($wrapper) {
            return new $wrapper($item);
        });
    }

    /**
     * Get the domain of the specified Institution.
     *
     * @return string
     * @throws \Exception
     */
    protected function getDomain(): string
    {
        $configName = $this->getInstitution()->getConfigName();

        if (array_key_exists($configName, config('isams.schools')) === false) {
            throw new \Exception("Configuration key '$configName' does not exist in 'isams.schools'");
        }

        return config("isams.schools.$configName.domain");
    }

    /**
     * Get the School to be queried.
     *
     * @return \spkm\isams\Contracts\Institution
     */
    protected function getInstitution(): Institution
    {
        return $this->institution;
    }

    /**
     * Validate the attributes.
     *
     * @param  array  $requiredAttributes
     * @param  array  $attributes
     *
     * @return bool
     * @throws \Exception
     */
    protected function validate(array $requiredAttributes, array $attributes): bool
    {
        foreach ($requiredAttributes as $requiredAttribute) {
            if (array_key_exists($requiredAttribute, $attributes) === false) {
                throw new ValidationException("'$requiredAttribute' is required by this endpoint.");
            }
        }

        return true;
    }

    /**
     * Generate the response.
     *
     * @param  int  $expectedStatusCode
     * @param  mixed  $response
     * @param  mixed  $data
     * @param  array  $errors
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function response(int $expectedStatusCode, $response, $data, array $errors = []): JsonResponse
    {
        $status = $response->getStatusCode() === $expectedStatusCode ? 'success' : 'error';
        $errors = empty($errors) === true ? null : $errors;

        $json = [
            'data' => $data,
            'status' => $status,
            'code' => $response->getStatusCode(),
            'errors' => $errors,
        ];

        if (isset($response->getHeaders()[ 'Location' ])) {
            $location = $response->getHeaders()[ 'Location' ][ 0 ];
            $id = ltrim(str_replace($this->endpoint, '', $location), '\//');

            $json[ 'location' ] = $location;
            if (! empty($id)) {
                $json[ 'id' ] = $id;
            }
        }

        return response()->json($json, $response->getStatusCode());
    }

    /**
     * @return \Carbon\Carbon
     */
    protected function getCacheDuration()
    {
        return config('isams.cacheDuration', now()->addHours(12));
    }
}
