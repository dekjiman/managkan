<?php

namespace Sites\Endpoint\Controllers\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ThrottleFilter implements FilterInterface
{
    private array $limits = [];

    public function __construct()
    {
        $this->limits = [
            'auth'       => ['limit' => 5, 'window' => 60],
            'api'        => ['limit' => 60, 'window' => 60],
            'default'    => ['limit' => 30, 'window' => 60],
        ];
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $key = $arguments[0] ?? 'default';
        $limitConfig = $this->limits[$key] ?? $this->limits['default'];

        $ip = $request->getIPAddress();
        $route = $request->uri->getPath();
        $cacheKey = "throttle:{$ip}:{$route}";

        $cache = \Config\Services::cache();
        $current = (int) $cache->get($cacheKey) ?: 0;

        if ($current >= $limitConfig['limit']) {
            $response = service('response');
            $response->setStatusCode(429);
            $response->setContentType('application/json', 'utf-8');
            $response->setBody(json_encode([
                'message' => 'Too many requests. Please try again later.',
            ]));
            $response->send();
            exit;
        }

        $cache->save($cacheKey, $current + 1, $limitConfig['window']);

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
