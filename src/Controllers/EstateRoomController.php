<?php

namespace spkm\isams\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\EstateRoom;

class EstateRoomController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/estates/rooms';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName() . 'estateRooms.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, config('isams.cacheDuration'), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'rooms', EstateRoom::class);
        });
    }
}
