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

    /*
    |--------------------------------------------------------------------------
    | Helper Functions
    |--------------------------------------------------------------------------
    |
    | Funciones auxiliares para el manejo de APIs
    |
    */

    'get_verificamex_config' => function() {
        return [
            'api_key' => config('utils.verificamex.api_key'),
            'base_url' => config('utils.verificamex.base_url'),
            'timeout' => config('utils.verificamex.timeout'),
            'headers' => config('utils.verificamex.headers')
        ];
    },

    'get_verificamex_headers' => function() {
        $config = config('utils.verificamex');
        $headers = $config['headers'];
        $headers['Authorization'] = 'Bearer ' . $config['api_key'];
        return $headers;
    }

];
