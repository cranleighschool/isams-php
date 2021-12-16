<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Employee;

class HumanResourcesEmployeeController extends Endpoint
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
        $this->endpoint = $this->getDomain() . '/api/humanresources/employees';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Collection
     *
     * @throws GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName() . 'hrEmployees.index';

        $decoded = json_decode($this->pageRequest($this->endpoint, 1));
        $items = collect($decoded->employees)->map(function ($item) {
            return new Employee($item);
        });

        $totalCount = $decoded->totalCount;
        $pageNumber = $decoded->page + 1;
        while ($pageNumber <= $decoded->totalPages) {
            $decoded = json_decode($this->pageRequest($this->endpoint, $pageNumber));

            collect($decoded->employees)->map(function ($item) use ($items) {
                $items->push(new Employee($item));
            });

            $pageNumber++;
        }

        if ($totalCount !== $items->count()) {
            throw new Exception($items->count() . ' items were returned instead of ' . $totalCount . ' as specified on page 1.');
        }

        $items = $this->sortBySurname($items);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($items) {
            return $items;
        });
    }

    /**
     * Sort by collection of Employee objects by surname.
     *
     * @param  Collection  $collection
     * @return Collection
     */
    private function sortBySurname(Collection $collection): Collection
    {
        $itemsArray = $collection->toArray();
        usort($itemsArray, function ($a, $b) {
            return strcmp($a->surname, $b->surname);
        });

        return Collect($itemsArray);
    }

    /**
     * Create a new resource.
     *
     * @param  array  $attributes
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function store(array $attributes): JsonResponse
    {
        $this->validate([
            'forename',
            'surname',
        ], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The employee has been created.');
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Employee
     *
     * @throws GuzzleException
     */
    public function show(int $id): Employee
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new Employee($decoded);
    }

    /**
     * Update the specified resource.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function update(int $id, array $attributes): JsonResponse
    {
        $this->validate([
            'title',
            'schoolInitials',
            'forename',
            'surname',
        ], $attributes);

        $currentData = (array) $this->show($id);

        $attributes = array_merge($currentData, $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint . '/' . $id, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The employee has been updated.');
    }
}
