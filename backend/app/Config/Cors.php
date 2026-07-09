<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends BaseConfig
{
    public array $default = [
        'allowedOrigins' => [],

        'allowedOriginsPatterns' => [],

        'supportsCredentials' => true,

        'allowedHeaders' => [
            'Authorization',
            'Content-Type',
            'Accept',
            'X-Requested-With',
            'Origin',
        ],

        'exposedHeaders' => [
            'Authorization',
            'Content-Type',
        ],

        'allowedMethods' => [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
            'OPTIONS',
        ],

        'maxAge' => 7200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->default['allowedOrigins'] = array_unique(array_filter([
            env('frontend.url', 'http://localhost:5173'),
            'http://localhost:3000',
            'http://localhost:5173',
            'http://localhost:8080',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:8080',
        ]));
    }
}
