<?php

namespace Sites\Endpoint\Controllers\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class OptionalAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!empty($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            try {
                $config = config('App');
                $secret = getenv('jwt.secret') ?: ($config->jwtSecret ?? 'managpro-jwt-secret');
                $decoded = JWT::decode($token, new Key($secret, 'HS256'));

                $request->user = (object) [
                    'id'    => $decoded->sub,
                    'email' => $decoded->email ?? null,
                    'name'  => $decoded->name ?? null,
                    'image' => $decoded->image ?? null,
                ];
                $request->token = $decoded;
            } catch (\Exception $e) {
            }
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
