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
            'client_id' => 'cranleigh',
            'domain' => 'https://isams.cranleigh.org',
            'client_secret' => env('CS_REST_API_SECRET'),
        ],

        'cranleighPreparatorySchool' => [
            'client_id' => 'cranleighprep',
            'domain' => 'https://isams.cranprep.org',
            'client_secret' => env('CPS_REST_API_SECRET'),
        ],

        'cranleighSandbox' => [
            'client_id' => 'cranleighprep',
            'domain' => 'https://isamsdev.cranprep.org',
            'client_secret' => env('DEV_REST_API_SECRET'),
        ],
    ],
];
