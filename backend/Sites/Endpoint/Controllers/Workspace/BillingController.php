<?php

namespace Sites\Endpoint\Controllers\Workspace;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Billing\Services\BillingService;
use Module\Workspace\Models\WorkspaceModel;

class BillingController extends BaseApiController
{
    protected BillingService $billingService;
    protected WorkspaceModel $workspaceModel;

    public function __construct()
    {
        $this->billingService = new BillingService();
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

    public function index(?string $wsPublicId = null)
    {
        try {
            $workspaceSlug = $wsPublicId ?: $this->getWorkspaceSlug();
            if (!$workspaceSlug) return $this->fail('Workspace identifier required', 400);
            $workspace = $this->workspaceModel->where('slug', $workspaceSlug)->where('deletedAt IS NULL')->first();
            if (!$workspace) {
                $workspace = $this->workspaceModel->where('publicId', $workspaceSlug)->where('deletedAt IS NULL')->first();
            }
            if (!$workspace) return $this->fail('Workspace not found', 404);

            $userId = $this->getUserId();
            $subscription = $this->billingService->getByWorkspace($workspace->id, $userId);
            return $this->respond($subscription, 200, 'Billing info retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function checkout(?string $wsPublicId = null)
    {
        try {
            $workspaceSlug = $wsPublicId ?: $this->getWorkspaceSlug();
            if (!$workspaceSlug) return $this->fail('Workspace identifier required', 400);
            $workspace = $this->workspaceModel->where('slug', $workspaceSlug)->where('deletedAt IS NULL')->first();
            if (!$workspace) {
                $workspace = $this->workspaceModel->where('publicId', $workspaceSlug)->where('deletedAt IS NULL')->first();
            }
            if (!$workspace) return $this->fail('Workspace not found', 404);

            $input = $this->getJsonInput();
            $userId = $this->getUserId();
            $planName = $input['plan'] ?? '';
            $result = $this->billingService->createCheckout($workspace->id, $userId, $planName);
            return $this->respond($result, 200, 'Checkout URL created successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function notification()
    {
        try {
            $input = $this->getJsonInput();
            $this->billingService->handleNotification($input);
            return $this->respondMessage('Notification processed successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
