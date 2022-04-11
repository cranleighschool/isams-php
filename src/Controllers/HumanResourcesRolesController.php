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

        return Cache::remember($key, $this->getCacheDuration(), function () {
            $decoded = json_decode($this->pageRequest($this->endpoint, 1));
            $items = collect($decoded->roles)->map(function ($item) {
                return new EmployeeRole($item);
            });

            $totalCount = $decoded->totalCount;
            $pageNumber = $decoded->page + 1;
            while ($pageNumber <= $decoded->totalPages) {
                $decoded = json_decode($this->pageRequest($this->endpoint, $pageNumber));

                collect($decoded->employees)->map(function ($item) use ($items) {
                    $items->push(new EmployeeRole($item));
                });

                $pageNumber++;
            }

            if ($totalCount !== $items->count()) {
                throw new Exception($items->count().' items were returned instead of '.$totalCount.' as specified on page 1.');
            }

            $items;
        });
    }

    /**
     * Create a new resource.
     *
     * @param  string  $roleName
     *
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function store(string $roleName): JsonResponse
    {
        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $roleName,

        ]);

        return $this->response(201, $response, 'The new Role has been created.');
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     *
     * @return EmployeeRole
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
     * @return void
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/humanresources/roles';
    }
}
