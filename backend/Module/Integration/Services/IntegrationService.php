<?php

namespace Module\Integration\Services;

use Module\Integration\Models\IntegrationModel;

class IntegrationService
{
    protected IntegrationModel $model;

    public function __construct()
    {
        $this->model = new IntegrationModel();
    }

    public function getByWorkspace(int $workspaceId): array
    {
        return $this->model
            ->select('id, integrationId, config, connected, connectedAt')
            ->where('workspaceId', $workspaceId)
            ->findAll();
    }

    public function setConnected(array $data): void
    {
        $existing = $this->model
            ->where('workspaceId', $data['workspaceId'])
            ->where('integrationId', $data['integrationId'])
            ->first();

        $payload = [
            'config'      => json_encode($data['config'] ?? []),
            'connected'   => $data['connected'] ?? 1,
            'updatedAt'   => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->model->update($existing->id, $payload);
        } else {
            $payload['publicId']      = generatePublicId();
            $payload['integrationId'] = $data['integrationId'];
            $payload['workspaceId']   = $data['workspaceId'];
            $payload['connectedBy']   = $data['createdBy'] ?? null;
            $payload['connectedAt']   = date('Y-m-d H:i:s');
            $this->model->insert($payload);
        }
    }
}
