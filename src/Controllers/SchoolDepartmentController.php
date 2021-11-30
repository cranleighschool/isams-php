<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\SchoolDepartment;

class SchoolDepartmentController extends Endpoint
{
    /*
     * @var array
     */
    protected $departmentTypes = [
        'teaching',
        'nonteaching',
    ];

    /**
     * Set the URL the request is made to.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain() . '/api/school/departments';
    }

    /**
     * Display a listing of the resource.
     *
     * @param  string  $departmentType  Teaching|NonTeaching
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function index(string $departmentType): JsonResponse
    {
        if (! in_array(strtolower($departmentType), $this->departmentTypes)) {
            return response()->json([
                'message' => 'Invalid Department Type; Valid types are Teaching or NonTeaching',
            ], 400);
        }

        $key = $this->institution->getConfigName() . sprintf('schoolDepartments%s.index', $departmentType);

        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $departmentType, ['headers' => $this->getHeaders()]);

        return Cache::remember($key, $this->getCacheDuration(), function () use ($response) {
            return $this->wrapJson($response->getBody()->getContents(), 'departments', SchoolDepartment::class);
        });
    }

    /**
     * Show the resource.
     *
     * @param  string  $departmentType
     * @param  int  $departmentId
     * @return JsonResponse|SchoolDepartment
     * @throws GuzzleException
     */
    public function show(string $departmentType, int $departmentId)
    {
        if (! in_array(strtolower($departmentType), $this->departmentTypes)) {
            return response()->json([
                'message' => 'Invalid Department Type; Valid types are Teaching or NonTeaching',
            ], 400);
        }

        $response = $this->guzzle->request('GET', $this->endpoint . '/' . $departmentType . '/' . $departmentId, ['headers' => $this->getHeaders()]);

        $decoded = json_decode($response->getBody()->getContents());

        return new SchoolDepartment($decoded);
    }
}
