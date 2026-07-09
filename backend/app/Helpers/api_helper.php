<?php

if (!function_exists('jsonResponse')) {
    function jsonResponse($data, int $statusCode = 200, string $message = null)
    {
        $response = service('response');
        $body = ['data' => $data];
        if ($message !== null) {
            $body['message'] = $message;
        }
        $response->setStatusCode($statusCode);
        $response->setContentType('application/json', 'utf-8');
        $response->setBody(json_encode($body, JSON_UNESCAPED_UNICODE));
        $response->send();
        exit;
    }
}

if (!function_exists('errorResponse')) {
    function errorResponse(string $message, int $statusCode = 400, $errors = null)
    {
        $response = service('response');
        $body = ['message' => $message];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        $response->setStatusCode($statusCode);
        $response->setContentType('application/json', 'utf-8');
        $response->setBody(json_encode($body, JSON_UNESCAPED_UNICODE));
        $response->send();
        exit;
    }
}

if (!function_exists('getJsonInput')) {
    function getJsonInput(): array
    {
        $request = service('request');
        $raw = $request->getBody() ?: '{}';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('getQueryParam')) {
    function getQueryParam(string $key, $default = null)
    {
        return service('request')->getGet($key) ?? $default;
    }
}

if (!function_exists('getCurrentUserId')) {
    function getCurrentUserId(): ?string
    {
        $request = service('request');
        return $request->user?->id ?? null;
    }
}
