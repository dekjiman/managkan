<?php

namespace Sites\Endpoint\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseApiController extends Controller
{
    protected $request;
    protected $response;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->response->setContentType('application/json', 'utf-8');
    }

    protected function respond($data, int $statusCode = 200, string $message = null)
    {
        $body = ['data' => $this->formatDates($data)];
        if ($message !== null) {
            $body['message'] = $message;
        }
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($body, JSON_UNESCAPED_UNICODE);
    }

    private function formatDates($data)
    {
        if (is_object($data)) {
            $data = clone $data;
            foreach ($data as $key => $value) {
                if (str_ends_with($key, 'At') && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                    $data->$key = str_replace(' ', 'T', $value) . 'Z';
                } elseif (is_object($value) || is_array($value)) {
                    $data->$key = $this->formatDates($value);
                }
            }
            return $data;
        }
        if (is_array($data)) {
            return array_map(fn($item) => $this->formatDates($item), $data);
        }
        return $data;
    }

    protected function respondMessage(string $message, int $statusCode = 200)
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON(['message' => $message], JSON_UNESCAPED_UNICODE);
    }

    protected function fail(string $message, int $statusCode = 400, $errors = null)
    {
        $body = ['message' => $message];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON($body, JSON_UNESCAPED_UNICODE);
    }

    protected function getJsonInput(): array
    {
        $raw = $this->request->getBody();
        if (empty($raw)) {
            return [];
        }
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON in request body', 400);
        }
        return is_array($data) ? $data : [];
    }

    protected function getUserId(): ?string
    {
        return $this->request->user?->id ?? null;
    }
}
