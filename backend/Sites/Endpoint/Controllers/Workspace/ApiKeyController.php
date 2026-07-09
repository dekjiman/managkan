<?php

namespace Sites\Endpoint\Controllers\Workspace;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\ApiKey\Services\ApiKeyService;
use Module\Workspace\Models\WorkspaceModel;

class ApiKeyController extends BaseApiController
{
    protected ApiKeyService $apiKeyService;
    protected WorkspaceModel $workspaceModel;

    public function __construct()
    {
        $this->apiKeyService = new ApiKeyService();
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
            $keys = $this->apiKeyService->getByWorkspace($ws->id);
            return $this->respond($keys, 200, 'API keys retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function store(?string $wsPublicId = null)
    {
        try {
            $slug = $wsPublicId ?: $this->getWorkspaceSlug();
            if (!$slug) return $this->fail('Workspace identifier required', 400);
            $ws = $this->resolveWorkspace($slug);
            $input = $this->getJsonInput();
            $input['workspaceId'] = $ws->id;
            $input['createdBy'] = $this->getUserId();
            $key = $this->apiKeyService->create($input);
            return $this->respond($key, 201, 'API key created successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function destroy(?string $wsPublicId = null, ?string $keyPublicId = null)
    {
        try {
            $publicId = $keyPublicId ?: ($this->request->getUri()->getSegment(3) ?: null);
            if (!$publicId) return $this->fail('API key identifier required', 400);
            $slug = $wsPublicId ?: $this->getWorkspaceSlug();
            if (!$slug) return $this->fail('Workspace identifier required', 400);
            $ws = $this->resolveWorkspace($slug);
            $this->apiKeyService->revoke($publicId, $ws->id);
            return $this->respondMessage('API key revoked successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
