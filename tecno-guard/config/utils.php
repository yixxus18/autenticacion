<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para las APIs externas utilizadas en la aplicación
    |
    */

    'verificamex' => [
        'api_key' => env('VERIFICAMEX_API_KEY'),
        'base_url' => 'https://api.verificamex.com',
        'timeout' => 30,
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'TecnoGuard/1.0'
        ]
    ],

];