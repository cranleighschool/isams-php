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
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/humanresources/employees';
    }

    /**
     * Display a listing of the resource.
     *
     *
     * @throws GuzzleException
     */
    public function index(): Collection
    {
        $key = $this->institution->getConfigName().'hrEmployees.index';

        return Cache::remember($key, $this->getCacheDuration(), function () {
            $decoded = json_decode($this->pageRequest($this->endpoint, 1, ['expand' => 'customFields']));
            $items = collect($decoded->employees)->map(function ($item) {
                return new Employee($item);
            });

            $totalCount = $decoded->totalCount;
            $pageNumber = $decoded->page + 1;
            while ($pageNumber <= $decoded->totalPages) {
                $decoded = json_decode($this->pageRequest($this->endpoint, $pageNumber, ['expand' => 'customFields']));

                collect($decoded->employees)->map(function ($item) use ($items) {
                    $items->push(new Employee($item));
                });

                $pageNumber++;
            }

            if ($totalCount !== $items->count()) {
                throw new Exception($items->count().' items were returned instead of '.$totalCount.' as specified on page 1.');
            }

            return $this->sortBySurname($items);
        });
    }

    /**
     * Sort by collection of Employee objects by surname.
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
     *
     * @throws GuzzleException
     */
    public function show(int $id): Employee
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
            'expand' => 'customFields',
        ]);

        $decoded = json_decode($response->getBody()->getContents());

        return new Employee($decoded);
    }

    /**
     * Update the specified resource.
     *
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

        $attributes = $this->fixEmptyNationalitiesData($attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The employee has been updated.');
    }

    /**
     * This is required because if Nationalities field is empty, it results in an array with one empty
     * item inside. Which when we push back to ISAMS responds with: '' is not a known nationality.
     * So what we are doing here is working out if that is the case, and if so unsetting the
     * attribute.
     */
    private function fixEmptyNationalitiesData(array $attributes): array
    {
        if (isset($attributes['nationalities'])) {
            $nationalities = $attributes['nationalities'];
            if (count($nationalities) == 1 && empty($nationalities[0])) {
                unset($attributes['nationalities']);
            }
        }

        return $attributes;
    }
}
