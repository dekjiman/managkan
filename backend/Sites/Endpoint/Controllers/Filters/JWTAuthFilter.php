<?php

namespace Sites\Endpoint\Controllers\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return service('response')
                ->setStatusCode(401)
                ->setContentType('application/json', 'utf-8')
                ->setBody(json_encode(['message' => 'Unauthorized']));
        }

        $token = substr($authHeader, 7);

        try {
            // Try multiple ways to get the secret
            $secret = getenv('jwt.secret');
            if (!$secret || $secret === false) {
                $secret = env('jwt.secret');
            }
            if (!$secret || $secret === false) {
                // Fallback: read .env directly
                $envFile = ROOTPATH . '.env';
                if (file_exists($envFile)) {
                    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($lines as $line) {
                        if (strpos(trim($line), 'jwt.secret') === 0) {
                            $parts = explode('=', $line, 2);
                            $secret = trim($parts[1] ?? '');
                            break;
                        }
                    }
                }
            }
            if (!$secret) {
                throw new \RuntimeException('JWT secret not configured. File: ' . $envFile . ' exists: ' . (file_exists($envFile) ? 'yes' : 'no'));
            }
            log_message('debug', 'JWT secret found, length: ' . strlen($secret));
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            $request->user = (object) [
                'id'    => $decoded->sub,
                'email' => $decoded->email ?? null,
                'name'  => $decoded->name ?? null,
                'image' => $decoded->image ?? null,
            ];
            $request->token = $decoded;
        } catch (\Throwable $e) {
            log_message('error', 'JWT Error: ' . $e->getMessage());
            return service('response')
                ->setStatusCode(401)
                ->setContentType('application/json', 'utf-8')
                ->setBody(json_encode(['message' => 'Invalid or expired token']));
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
