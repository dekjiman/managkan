<?php

namespace Sites\Endpoint\Controllers\Plan;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Plan\Services\PlanService;

class PlanController extends BaseApiController
{
    protected PlanService $planService;

    public function __construct()
    {
        $this->planService = new PlanService();
    }

    public function index()
    {
        try {
            $plans = $this->planService->getAll();
            return $this->respond($plans, 200, 'Plans retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function show(string $name = '')
    {
        try {
            if (empty($name)) {
                return $this->fail('Plan name is required', 400);
            }
            $plan = $this->planService->getByName($name);
            return $this->respond($plan, 200, 'Plan retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
