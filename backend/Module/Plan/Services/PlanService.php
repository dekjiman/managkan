<?php

namespace Module\Plan\Services;

use Module\Plan\Models\PlanModel;

class PlanService
{
    protected PlanModel $model;

    public function __construct()
    {
        $this->model = new PlanModel();
    }

    private function format(object $plan): object
    {
        $plan->id = (int)$plan->id;
        $plan->price = (int)$plan->price;
        $plan->boardLimit = (int)$plan->boardLimit;
        $plan->memberLimit = (int)$plan->memberLimit;
        $plan->workspaceLimit = (int)$plan->workspaceLimit;
        $plan->storageLimit = (int)$plan->storageLimit;
        $plan->isActive = (bool)$plan->isActive;
        $plan->features = is_string($plan->features) ? json_decode($plan->features) : ($plan->features ?? []);
        return $plan;
    }

    public function getAll(): array
    {
        $plans = $this->model
            ->where('isActive', 1)
            ->findAll();
        return array_map(fn($p) => $this->format($p), $plans);
    }

    public function getByName(string $name): object
    {
        $plan = $this->model->where('name', $name)->where('isActive', 1)->first();
        if (!$plan) throw new \RuntimeException('Plan not found', 404);

        return $this->format($plan);
    }
}
