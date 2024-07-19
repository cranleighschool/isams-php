<?php

/*
|--------------------------------------------------------------------------
| iSAMS REST API Configuration
|--------------------------------------------------------------------------
*/

return [
    /*
    |--------------------------------------------------------------------------
    | Schools
    |--------------------------------------------------------------------------
    |
    | Add schools to the schools array. The key must match the output of getConfigName() method on the object
    | implementing the Institution interface. See "spkm\isams\School" as an example;

    |
    */
    'schools' => [
        'cranleighSchool' => [
            'client_id' => env('CS_REST_API_CLIENT'),
            'domain' => 'https://isams.cranleigh.org',
            'client_secret' => env('CS_REST_API_SECRET'),
        ],

        'cranleighPreparatorySchool' => [
            'client_id' => env('CPS_REST_API_CLIENT', 'cranleighprep'),
            'domain' => 'https://isams.cranprep.org',
            'client_secret' => env('CPS_REST_API_SECRET'),
        ],

        'cranleighSandbox' => [
            'client_id' => env('DEV_REST_API_CLIENT'),
            'domain' => 'https://isamsdev.cranprep.org',
            'client_secret' => env('DEV_REST_API_SECRET'),
        ],
    ],

    'cacheDuration' => now()->addHours(12),
];
