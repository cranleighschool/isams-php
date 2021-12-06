<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\RewardAndConductCategory;

class RewardAndConductCategoryController extends Endpoint
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
        $this->endpoint = $this->getDomain() . '/api/rewardsAndConduct/moduleTypes';
    }

    /**
     * Retrieves all categories associated with a module type.
     *
     * @param  int  $moduleTypeId
     * @return Collection
     *
     * @throws GuzzleException
     */
    public function index(int $moduleTypeId): Collection
    {
        $key = $this->institution->getConfigName() . 'rewardsAndConductCategories.index'.$moduleTypeId;

        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $moduleTypeId . '/categories', ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'categories', RewardAndConductCategory::class);
        });
    }

    /**
     * Retrieves a category associated with a module type.
     *
     * @param  string  $moduleTypeId
     * @param  int  $categoryId
     * @return RewardAndConductCategory
     *
     * @throws GuzzleException
     */
    public function show(string $moduleTypeId, int $categoryId): RewardAndConductCategory
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $moduleTypeId . '/categories/' . $categoryId, ['headers' => $this->getHeaders()]);

        $data = json_decode($response->getBody()->getContents());

        return new RewardAndConductCategory($data);
    }
}
