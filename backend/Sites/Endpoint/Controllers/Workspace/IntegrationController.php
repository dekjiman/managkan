<?php

namespace Sites\Endpoint\Controllers\Workspace;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Integration\Services\IntegrationService;
use Module\Workspace\Models\WorkspaceModel;

class IntegrationController extends BaseApiController
{
    protected IntegrationService $integrationService;
    protected WorkspaceModel $workspaceModel;

    public function __construct()
    {
        $this->integrationService = new IntegrationService();
        $this->workspaceModel = new WorkspaceModel();
    }

    private function getWorkspaceSlug(): ?string
    {
        $qp = $this->request->getGet('workspaceId') ?: $this->request->getGet('workspaceSlug');
        if ($qp) return $qp;
        if ($this->request->getUri()->getSegment(1) === 'workspaces') {
            return $this->request->getUri()->getSegment(2);
        }
        return null;
    }

    private function resolveWorkspace(string $slug): object
    {
        $ws = $this->workspaceModel
            ->where('publicId', $slug)
            ->where('deletedAt IS NULL')
            ->first();
        if (!$ws) {
            $ws = $this->workspaceModel
                ->where('slug', $slug)
                ->where('deletedAt IS NULL')
                ->first();
        }
        if (!$ws) {
            throw new \RuntimeException('Workspace not found', 404);
        }
        return $ws;
    }

    public function index(?string $wsPublicId = null)
    {
        try {
            $slug = $wsPublicId ?: $this->getWorkspaceSlug();
            if (!$slug) return $this->fail('Workspace identifier required', 400);
            $ws = $this->resolveWorkspace($slug);
            $integrations = $this->integrationService->getByWorkspace($ws->id);
            return $this->respond($integrations, 200, 'Integrations retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function toggle(?string $wsPublicId = null)
    {
        try {
            $slug = $wsPublicId ?: $this->getWorkspaceSlug();
            if (!$slug) return $this->fail('Workspace identifier required', 400);
            $ws = $this->resolveWorkspace($slug);
            $input = $this->getJsonInput();
            $input['integrationId'] = $input['provider'] ?? '';
            $input['workspaceId'] = $ws->id;
            $input['createdBy'] = $this->getUserId();
            $this->integrationService->setConnected($input);
            return $this->respondMessage('Integration toggled successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
