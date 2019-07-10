<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\Wrappers\File;

class StorageFileController extends Endpoint
{
    /**
     * Set the URL the request is made to
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        //Note: /api/storage/files does not work as of 2018/08/21 - iSAMS aware.
        //Therefore using alternative below '/api/storage/files/download?'
        $this->endpoint = $this->getDomain().'/api/storage/files/download';
    }

    /**
     * Show the specified resource
     *
     * @param string $path
     * @param string $name
     * @return binary|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(string $path, string $name)
    {
        $url = $this->endpoint.'?path='.$path.'&fileName='.$name;

        $response = $this->guzzle->request('GET', $url, ['headers' => $this->getHeaders()]);

        return $response->getBody()->getContents();
    }
}
