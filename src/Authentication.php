<?php

namespace spkm\isams;

use \GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\Cache;
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
     * @param \spkm\isams\Contracts\Institution $institution
     * @throws \Exception
     */
    public function __construct(Institution $institution)
    {
        $this->getConfig($institution);
    }

    /**
     * Get an authentication token from the cache or request a new one.
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken()
    {
        if (Cache::store('file')->has($this->cacheKey)):
            return Cache::store('file')->get($this->cacheKey);
        endif;

        return $this->requestNewToken();
    }

    /**
     * Request a new authentication token.
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function requestNewToken()
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
                'scope' => 'api',
            ],
        ]);

        if($response->getStatusCode() !== 200){
            throw new \Exception('Unable to request new authentication token, invalid response (Error 500)');
        }

        $data = json_decode($response->getBody()->getContents());

        return $this->cache($data->access_token, $data->expires_in);
    }

    /**
     * Save the access token to the cache & return it for use.
     *
     * @param string $accessToken
     * @param int $expiry
     * @return string
     */
    private function cache($accessToken, $expiry)
    {
        $minutes = ($expiry / 60);
        Cache::store('file')->put($this->cacheKey, $accessToken, now()->addMinutes($minutes));

        return Cache::store('file')->get($this->cacheKey);
    }

    /**
     * Set the client settings.
     *
     * @param \spkm\isams\Contracts\Institution $institution
     * @return void
     * @throws \Exception
     */
    private function getConfig(Institution $institution)
    {
        $configName = $institution->getConfigName();
        if (array_key_exists($configName, config('isams.schools')) === false):
            throw new \Exception("Configuration key '$configName' does not exist in 'isams.schools'");
        endif;

        $this->clientId = config("isams.schools.$configName.client_id");
        $this->authenticationUrl = config("isams.schools.$configName.domain").'/main/sso/idp/connect/token';
        $this->clientSecret = config("isams.schools.$configName.client_secret");
        $this->cacheKey = $configName.'RestApiAccessToken';
    }
}
