<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\EmployeeRole;

class HumanResourcesRolesController extends Endpoint
{
    public function index(): Collection
    {
        $key = $this->institution->getConfigName().'hrRoles.index';

        $response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'roles', EmployeeRole::class);
        });
    }

    /**
     * @throws \Illuminate\Support\ItemNotFoundException
     */
    public function searchByRoleName(string $string): EmployeeRole
    {
        $list = $this->index()->where('name', '=', $string);

        return $list->firstOrFail();
    }

    /**
     * Create a new resource.
     *
     *
     * @throws GuzzleException
     */
    public function store(string $roleName): JsonResponse
    {
        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => [
                'name' => $roleName,
            ],

        ]);

        return $this->response(201, $response, 'The new Role has been created.');
    }

    /**
     * Show the specified resource.
     *
     *
     * @throws GuzzleException
     */
    public function show(int $id): EmployeeRole
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
        ]);

        $decoded = json_decode($response->getBody()->getContents());

        return new EmployeeRole($decoded);
    }

    /**
     * Set the URL the request is made to.
     *
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/humanresources/roles';
    }
}
