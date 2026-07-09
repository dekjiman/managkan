<?php

namespace Sites\Endpoint\Controllers\Workspace;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Webhook\Services\WebhookService;
use Module\Workspace\Models\WorkspaceModel;

class WebhookController extends BaseApiController
{
    protected WebhookService $webhookService;
    protected WorkspaceModel $workspaceModel;

    public function __construct()
    {
        $this->webhookService = new WebhookService();
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
            $webhooks = $this->webhookService->getByWorkspace($ws->id);
            return $this->respond($webhooks, 200, 'Webhooks retrieved successfully');
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
            $webhook = $this->webhookService->create($input);
            return $this->respond($webhook, 201, 'Webhook created successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function destroy(?string $wsPublicId = null, ?string $webhookPublicId = null)
    {
        try {
            $publicId = $webhookPublicId ?: ($this->request->getUri()->getSegment(4) ?: $this->request->getUri()->getSegment(2));
            if (!$publicId) return $this->fail('Webhook identifier required', 400);
            $slug = $wsPublicId ?: $this->getWorkspaceSlug();
            if (!$slug) return $this->fail('Workspace identifier required', 400);
            $ws = $this->resolveWorkspace($slug);
            $this->webhookService->delete($publicId, $ws->id);
            return $this->respondMessage('Webhook deleted successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function test(?string $wsPublicId = null, ?string $webhookPublicId = null)
    {
        try {
            $publicId = $webhookPublicId ?: ($this->request->getUri()->getSegment(4) ?: $this->request->getUri()->getSegment(2));
            if (!$publicId) return $this->fail('Webhook identifier required', 400);
            $result = $this->webhookService->test($publicId);
            return $this->respond($result, 200, 'Webhook test completed');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
