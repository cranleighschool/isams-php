<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\Employee;
use spkm\isams\Wrappers\EmployeePhoto;

class HumanResourcesEmployeeController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     * @throws \Exception
     */
    protected function setEndpoint()
    {
        $this->endpoint = $this->getDomain() . '/api/humanresources/employees';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
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
            throw new \Exception($items->count() . ' items were returned instead of ' . $totalCount . ' as specified on page 1.');
        }

        $items = $this->sortBySurname($items);

        return Cache::remember($key, config('isams.cacheDuration'), function () use ($items) {
            return $items;
        });
    }

    /**
     * Sort by collection of Employee objects by surname.
     *
     * @param \Illuminate\Support\Collection $collection
     * @return \Illuminate\Support\Collection
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
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
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
     * @param int $id
     * @return \spkm\isams\Wrappers\Employee
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(int $id): Employee
    {
        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new Employee($decoded);
    }

    /**
     * Gets the Current Photo for the Employee.
     *
     * @param int $id
     * @param int $quality
     * @return EmployeePhoto
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCurrentPhoto(int $id, int $quality = 75): EmployeePhoto
    {
        /**
         * At the moment this package doesn't auto-include Intervention, so we need to check for its existence first.
         */
        if (! method_exists(ImageManagerStatic::class, 'make')) {
            throw new Exception('This method requires Intervention/Image package.', 500);
        }

        try {
            /**
             * Hello ISAMS!
             */
            $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id . '/photos/current', ['headers' => $this->getHeaders()]);

            /**
             * Get the Image and Save it to Storage.
             */
            $image = ImageManagerStatic::make($response->getBody()->getContents());
            $data = $image->encode('jpg', $quality);
            $save = Storage::put($id . '.jpg', $data);

            /**
             * Grab the image out of storage and encode it as a Data URL
             * Then Delete the image from Storage. (Like we'd never know it was there!).
             */
            $image = storage_path('app/' . $id . '.jpg');
            $image = ImageManagerStatic::make($image)->encode('data-url');
            Storage::delete($id . '.jpg');
        } catch (RequestException $exception) {
            $image = ['error' => json_decode($exception->getResponse()->getBody()->getContents())];
        }

        /**
         * Return an instance of the EmployeePhoto class.
         */
        return new EmployeePhoto($image);
    }

    /**
     * Update the specified resource.
     *
     * @param int $id
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(int $id, array $attributes): JsonResponse
    {
        $this->validate([
            'forename',
            'surname',
        ], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint . '/' . $id, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The employee has been updated.');
    }
}
