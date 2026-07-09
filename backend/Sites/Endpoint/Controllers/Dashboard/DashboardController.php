<?php

namespace Sites\Endpoint\Controllers\Dashboard;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Dashboard\Services\DashboardService;

class DashboardController extends BaseApiController
{
    protected DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    public function index(?string $workspacePublicId = null)
    {
        try {
            $userId = $this->getUserId();
            $data = $this->dashboardService->getData($userId, $workspacePublicId);
            return $this->respond($data, 200, 'Dashboard data retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
