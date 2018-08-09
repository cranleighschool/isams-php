<?php

namespace spkm\isams\Controllers;

use spkm\isams\Endpoint;
use spkm\isams\Wrappers\School;
use spkm\isams\Contracts\Institution;
use Illuminate\Support\Facades\Cache;

class OtherSchoolController extends Endpoint
{
    /**
     * @var \spkm\isams\Contracts\Institution
     */
    private $institution;

    /**
     * @var string
     */
    protected $endpoint;

    public function __construct(Institution $institution)
    {
        $this->institution = $institution;
        $this->setGuzzle();
        $this->setEndpoint();
    }

    /**
     * Get the School to be queried
     *
     * @return \spkm\Isams\Contracts\Institution
     */
    protected function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set the URL the request is made to
     *
     * @return void
     * @throws \Exception
     */
    private function setEndpoint()
    {
        $this->endpoint = $this->getDomain().'/api/otherschools';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        $key = $this->institution->getConfigName().'otherSchools.index';

        $decoded = json_decode($this->pageRequest($this->endpoint, 1));
        $items = collect($decoded->otherSchools)->map(function ($item) {
            return new School($item, $this->institution);
        });

        $totalCount = $decoded->totalCount;
        $pageNumber = $decoded->page + 1;
        while ($pageNumber <= $decoded->totalPages):
            $decoded = json_decode($this->pageRequest($this->endpoint, $pageNumber));

            collect($decoded->otherSchools)->map(function ($item) use ($items) {
                $items->push(new School($item, $this->institution));
            });

            $pageNumber++;
        endwhile;

        if ($totalCount !== $items->count()):
            throw new \Exception($items->count().' items were returned instead of '.$totalCount.' as specified on page 1.');
        endif;

        return Cache::remember($key, 10080, function () use ($items) {
            return $items;
        });
    }

    /**
     * Create a new resource.
     *
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(array $attributes)
    {
        $this->validate([
            'schoolName',
            'schoolCode',
            'schoolTelephone',
            'postcode',
        ], $attributes);

        $response = $this->guzzle->request('POST', $this->endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(201, $response, 'The school has been created.');
    }

    /**
     * Show the specified resource
     *
     * @param int $id
     * @return \spkm\isams\Wrappers\School
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function show(int $id)
    {
        $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new School($decoded, $this->institution);
    }

    /**
     * Update the specified resource.
     *
     * @param int $id
     * @param array $attributes
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(int $id, array $attributes)
    {
        $this->validate([
            'schoolName',
            'schoolCode',
            'schoolTelephone',
            'postcode',
        ], $attributes);

        $response = $this->guzzle->request('PUT', $this->endpoint.'/'.$id, [
            'headers' => $this->getHeaders(),
            'json' => $attributes,
        ]);

        return $this->response(200, $response, 'The school has been updated.');
    }
}
