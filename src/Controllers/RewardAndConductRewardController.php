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
     * Retrieves all rewards associated with a student.
     *
     * @param  string  $schoolId
     * @return Collection
     *
     * @throws GuzzleException
     */
    public function index(string $schoolId): Collection
    {
        $key = $this->institution->getConfigName() . 'rewardsAndConduct.index';

        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $schoolId . '/rewards', ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'rewards', RewardAndConductReward::class);
        });
    }

    /**
     * Retrieves a students rewards.
     *
     * @param  string  $schoolId
     * @param  int  $rewardId
     * @return RewardAndConductReward
     *
     * @throws GuzzleException
     */
    public function show(string $schoolId, int $rewardId): RewardAndConductReward
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $schoolId . '/rewards/' . $rewardId, ['headers' => $this->getHeaders()]);

        $data = json_decode($response->getBody()->getContents());

        return new RewardAndConductReward($data);
    }
}
