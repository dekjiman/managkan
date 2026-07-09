<?php

namespace Module\Webhook\Services;

use Module\Webhook\Models\WebhookModel;

class WebhookService
{
    protected WebhookModel $model;

    public function __construct()
    {
        $this->model = new WebhookModel();
    }

    public function getByWorkspace(int $workspaceId): array
    {
        return $this->model
            ->select('publicId, url, events, active, createdAt')
            ->where('workspaceId', $workspaceId)
            ->findAll();
    }

    public function create(array $data): array
    {
        $secret = bin2hex(random_bytes(16));

        $this->model->insert([
            'publicId'    => generatePublicId(),
            'url'         => $data['url'],
            'secret'      => $secret,
            'events'      => json_encode($data['events'] ?? []),
            'isActive'    => 1,
            'workspaceId' => $data['workspaceId'],
            'createdBy'   => $data['createdBy'] ?? null,
            'createdAt'   => date('Y-m-d H:i:s'),
        ]);

        return ['publicId' => $this->model->getInsertID(), 'secret' => $secret];
    }

    public function delete(string $publicId, int $workspaceId): void
    {
        $webhook = $this->model->where('publicId', $publicId)->where('workspaceId', $workspaceId)->first();
        if (!$webhook) throw new \RuntimeException('Webhook not found', 404);

        $this->model->delete($webhook->id);
    }

    public function test(string $publicId): array
    {
        $webhook = $this->model->where('publicId', $publicId)->first();
        if (!$webhook) throw new \RuntimeException('Webhook not found', 404);

        $host = parse_url($webhook->url, PHP_URL_HOST);
        if (!$host) throw new \RuntimeException('Invalid webhook URL', 400);

        $ip = gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw new \RuntimeException('SSRF protection: private or reserved IP not allowed', 403);
        }

        $ch = curl_init($webhook->url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['event' => 'ping', 'timestamp' => date('Y-m-d H:i:s')]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_FOLLOWLOCATION => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) throw new \RuntimeException('Webhook test failed: ' . $error, 502);

        return ['statusCode' => $httpCode, 'response' => $response];
    }
}
