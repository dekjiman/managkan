<?php

namespace Sites\Endpoint\Controllers;

class HealthController extends BaseApiController
{
    public function index()
    {
        return $this->respond([
            'status'    => 'ok',
            'timestamp' => date('c'),
        ], 200, 'Service is healthy');
    }
}
