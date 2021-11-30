<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\RewardAndConductReward;

class RewardAndConductRewardController extends Endpoint
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
        $this->endpoint = $this->getDomain() . '/api/rewardsAndConduct/students';
    }

    /**
     * Display a listing of the resource.
     *
     * @param  string  $id
     * @return Collection
     *
     * @throws GuzzleException
     */
    public function index(string $id): Collection
    {
        $key = $this->institution->getConfigName() . 'rewardsAndConduct.index';

        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id . '/rewards', ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'items', RewardAndConductReward::class);
        });
    }
}
