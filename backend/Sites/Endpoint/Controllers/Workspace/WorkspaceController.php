<?php

namespace Sites\Endpoint\Controllers\Workspace;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Workspace\Services\WorkspaceService;

class WorkspaceController extends BaseApiController
{
    protected WorkspaceService $workspaceService;

    public function __construct()
    {
        $this->workspaceService = new WorkspaceService();
    }

    public function index()
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $workspaces = $this->workspaceService->getByUser($userId);
            return $this->respond($workspaces, 200, 'Workspaces retrieved');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function show(string $publicId)
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $workspace = $this->workspaceService->getById($publicId, $userId);
            return $this->respond($workspace, 200, 'Workspace retrieved');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function store()
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $input = $this->getJsonInput();
            $workspace = $this->workspaceService->create($input, $userId);
            return $this->respond($workspace, 201, 'Workspace created');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function update(string $publicId)
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $input = $this->getJsonInput();
            $workspace = $this->workspaceService->update($publicId, $input, $userId);
            return $this->respond($workspace, 200, 'Workspace updated');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function destroy(string $publicId)
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $this->workspaceService->delete($publicId, $userId);
            return $this->respond(null, 200, 'Workspace deleted');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function checkSlugAvailability()
    {
        try {
            $slug = $this->request->getGet('workspaceSlug') ?: $this->request->getGet('slug');

            if (empty($slug)) {
                return $this->fail('Slug parameter is required', 400);
            }

            $result = $this->workspaceService->checkSlugAvailability($slug);
            return $this->respond($result, 200, 'Slug availability checked');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
