<?php

namespace Module\ApiKey\Services;

use Module\ApiKey\Models\ApiKeyModel;

class ApiKeyService
{
    protected ApiKeyModel $model;

    public function __construct()
    {
        $this->model = new ApiKeyModel();
    }

    public function getByWorkspace(int $workspaceId): array
    {
        return $this->model
            ->select('publicId, name, keyPrefix, permissions, active, lastUsedAt, createdAt')
            ->where('workspaceId', $workspaceId)
            ->orderBy('createdAt', 'DESC')
            ->findAll();
    }

    public function create(array $data): array
    {
        $prefix = 'mck_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $secret = bin2hex(random_bytes(24));
        $fullKey = $prefix . '.' . $secret;
        $hash = hash('sha256', $fullKey);

        $this->model->insert([
            'publicId'    => generatePublicId(),
            'name'        => $data['name'],
            'keyHash'     => $hash,
            'keyPrefix'   => $prefix,
            'permissions' => json_encode($data['permissions'] ?? ['read']),
            'active'      => 1,
            'workspaceId' => $data['workspaceId'],
            'createdBy'   => $data['createdBy'] ?? null,
            'createdAt'   => date('Y-m-d H:i:s'),
        ]);

        return ['key' => $fullKey, 'prefix' => $prefix];
    }

    public function revoke(string $publicId, int $workspaceId): void
    {
        $key = $this->model->where('publicId', $publicId)->where('workspaceId', $workspaceId)->first();
        if (!$key) throw new \RuntimeException('API key not found', 404);

        $this->model->update($key->id, [
            'active'    => 0,
            'revokedAt' => date('Y-m-d H:i:s'),
        ]);
    }
}
