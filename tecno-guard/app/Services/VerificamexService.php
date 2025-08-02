<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class VerificamexService
{
    protected $apiKey;
    protected $baseUrl;
    protected $timeout;
    protected $baseHeaders;

    public function __construct()
    {
        // Accedemos a la configuración desde config/utils.php
        $config = Config::get('utils.verificamex');
        $this->apiKey = $config['api_key'];
        $this->baseUrl = $config['base_url'];
        $this->timeout = $config['timeout'];
        $this->baseHeaders = $config['headers'];
    }

    /**
     * Genera los encabezados de autenticación para la API.
     *
     * @return array
     */
    public function getAuthHeaders(): array
    {
        $headers = $this->baseHeaders;
        $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        return $headers;
    }

    /**
     * Obtiene la URL base de la API.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

}
