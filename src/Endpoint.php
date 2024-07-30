<?php

namespace spkm\isams;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use spkm\isams\Contracts\Institution;
use spkm\isams\Exceptions\ValidationException;

abstract class Endpoint
{
    /**
     * @var Guzzle
     */
    protected Guzzle $guzzle;

    /**
     * @var Institution
     */
    protected Institution $institution;

    /**
     * @var string
     */
    protected string $endpoint;

    /**
     * @throws Exception
     */
    public function __construct(?Institution $institution = null)
    {
        $this->setInstitution($institution);
        $this->setGuzzle();
        $this->setEndpoint();
    }

    /**
     * @throws Exception
     */
    protected function setInstitution(Institution $institution): void
    {
        $this->institution = $institution;

        if ($this->institution === null) {
            if (function_exists('defaultIsamsInstitution')) {
                $this->institution = defaultIsamsInstitution();
            } else {
                throw new Exception('No Institution provided and no default Institution set.');
            }
        }
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
     */
    abstract protected function setEndpoint(): void;

    /**
     * Get a specific page from the api.
     *
     * @return mixed
     *
     * @throws GuzzleException
     */
    public function pageRequest(string $url, int $page, array $queryArgs = [])
    {
        $response = $this->guzzle->request('GET', $url, [
            'query' => array_merge(['page' => $page], $queryArgs),
            'headers' => $this->getHeaders(),
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * Get the Guzzle headers for a request.
     *
     * @return array
     *
     * @throws GuzzleException
     */
    protected function getHeaders()
    {
        return [
            'Authorization' => 'Bearer '.$this->getAccessToken(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Get an access token for the specified Institution.
     *
     *
     * @throws GuzzleException
     * @throws Exception
     */
    protected function getAccessToken(): string
    {
        return (new Authentication($this->getInstitution()))->getToken();
    }

    /**
     * Wrap the json returned by the API.
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
     *
     * @throws Exception
     */
    protected function getDomain(): string
    {
        $configName = $this->getInstitution()->getConfigName();

        if (array_key_exists($configName, config('isams.schools')) === false) {
            throw new Exception("Configuration key '$configName' does not exist in 'isams.schools'");
        }

        return config("isams.schools.$configName.domain");
    }

    /**
     * Get the School to be queried.
     */
    protected function getInstitution(): Institution
    {
        return $this->institution;
    }

    /**
     * Validate the attributes.
     *
     *
     * @throws Exception
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
     * @param  mixed  $response
     * @param  mixed  $data
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

        if (isset($response->getHeaders()['Location'])) {
            $location = $response->getHeaders()['Location'][0];
            $id = ltrim(str_replace($this->endpoint, '', $location), '\//');

            $json['location'] = $location;
            if (! empty($id)) {
                $json['id'] = $id;
            }
        }

        return response()->json($json, $response->getStatusCode());
    }

    protected function getCacheDuration(): Carbon
    {
        return config('isams.cacheDuration', now()->addHours(12));
    }
}
