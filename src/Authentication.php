<?php

namespace spkm\isams;

use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;
use spkm\isams\Contracts\Institution;

class Authentication
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $authenticationUrl;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @param  Institution  $institution
     *
     * @throws Exception
     */
    public function __construct(Institution $institution)
    {
        $this->getConfig($institution);
    }

    /**
     * Get an authentication token from the cache or request a new one.
     *
     * @return string
     *
     * @throws GuzzleException|InvalidArgumentException
     */
    public function getToken(): string
    {
        if (Cache::store('file')->has($this->cacheKey)) {
            return Cache::store('file')->get($this->cacheKey);
        }

        return $this->requestNewToken();
    }

    /**
     * Request a new authentication token.
     *
     * @return string
     *
     * @throws GuzzleException
     */
    private function requestNewToken(): string
    {
        $guzzle = new Guzzle();

        $response = $guzzle->request('POST', $this->authenticationUrl, [
            'headers' => [
                'cache-control' => 'no-cache',
                'Content-type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
//                'scope' => 'api', // POST MERGER SEEMINGLY TAKE THIS OUT
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Unable to request new authentication token, invalid response (Error 500)');
        }

        $data = json_decode($response->getBody()->getContents());

        return $this->cache($data->access_token, $data->expires_in);
    }

    /**
     * Save the access token to the cache & return it for use.
     *
     * @param  string  $accessToken
     * @param  int  $expiry
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function cache(string $accessToken, int $expiry): string
    {
        $minutes = (int) ($expiry / 60) - 5;
        Cache::store('file')->put($this->cacheKey, $accessToken, now()->addMinutes($minutes));

        return Cache::store('file')->get($this->cacheKey);
    }

    /**
     * Set the client settings.
     *
     * @param  Institution  $institution
     * @return void
     *
     * @throws Exception
     */
    private function getConfig(Institution $institution): void
    {
        $configName = $institution->getConfigName();
        if (array_key_exists($configName, config('isams.schools')) === false) {
            throw new Exception("Configuration key '$configName' does not exist in 'isams.schools'");
        }

        $this->clientId = config("isams.schools.$configName.client_id");
        $this->authenticationUrl = config("isams.schools.$configName.domain") . '/auth/connect/token';
        $this->clientSecret = config("isams.schools.$configName.client_secret");
        $this->cacheKey = $configName . 'RestApiAccessToken';
    }
}
