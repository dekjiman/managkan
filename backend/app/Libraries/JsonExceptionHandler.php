<?php

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class JsonExceptionHandler
{
    public static function handle(Throwable $e, RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $logger->error($e->getMessage(), [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $statusCode = 500;
        $message = 'Internal Server Error';

        if (method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        }
        if (method_exists($e, 'getMessage')) {
            $message = $e->getMessage();
        }

        $response->setStatusCode($statusCode);
        $response->setContentType('application/json', 'utf-8');
        $response->setBody(json_encode([
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE));
    }
}
