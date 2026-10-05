<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | SahiCheck API Key
    |--------------------------------------------------------------------------
    |
    | Your SahiCheck API key for authentication. Get your key from the
    | SahiCheck dashboard: https://sahicheck.com/dashboard
    |
    */
    'api_key' => env('SAHICHECK_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | SahiCheck API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the SahiCheck REST API. Defaults to the production URL.
    |
    */
    'base_url' => env('SAHICHECK_BASE_URL', 'https://api.sahicheck.com'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for responses from SahiCheck.
    |
    */
    'timeout' => (int) env('SAHICHECK_TIMEOUT', 10),
];
