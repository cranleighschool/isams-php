<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\RewardAndConductReward;

class RewardAndConductRewardController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/rewardsAndConduct/students';
    }

    /**
     * Retrieves all rewards associated with a student.
     *
     *
     * @throws GuzzleException
     */
    public function index(string $schoolId): Collection
    {
        $key = $this->institution->getConfigName().'rewardsAndConduct.index'.$schoolId;

        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$schoolId.'/rewards', ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'rewards', RewardAndConductReward::class);
        });
    }

    /**
     * Retrieves a students rewards.
     *
     *
     * @throws GuzzleException
     */
    public function show(string $schoolId, int $rewardId): RewardAndConductReward
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$schoolId.'/rewards/'.$rewardId, ['headers' => $this->getHeaders()]);

        $data = json_decode($response->getBody()->getContents());

        return new RewardAndConductReward($data);
    }

    /**
     * Create a new resource.
     *
     *
     * @throws GuzzleException
     */
    public function store(string $pupilId, array $attributes): JsonResponse
    {
        $this->validate([
            'moduleTypeId',
            'categoryId',
            'date',
            'TeacherId',
        ], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint.'/'.$pupilId.'/rewards', [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The award has been created.');
    }
}
