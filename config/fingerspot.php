<?php

return [
    'token' => env('FINGERSPOT_API_TOKEN'),
    'base_url' => rtrim(env('FINGERSPOT_BASE_URL', 'https://developer.fingerspot.io/api'), '/'),
    'timeout' => (int) env('FINGERSPOT_HTTP_TIMEOUT', 30),

    'endpoints' => [
        'get_userinfo' => env('FINGERSPOT_ENDPOINT_GET_USERINFO', '/get_userinfo'),
        'set_userinfo' => env('FINGERSPOT_ENDPOINT_SET_USERINFO', '/set_userinfo'),
        'delete_userinfo' => env('FINGERSPOT_ENDPOINT_DELETE_USERINFO', '/delete_userinfo'),
        'get_all_pin' => env('FINGERSPOT_ENDPOINT_GET_ALL_PIN', '/get_all_pin'),
        'get_attlog' => env('FINGERSPOT_ENDPOINT_GET_ATTLOG', '/get_attlog'),
        'set_time' => env('FINGERSPOT_ENDPOINT_SET_TIME', '/set_time'),
    ],
];