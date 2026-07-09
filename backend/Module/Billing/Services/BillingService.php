<?php

namespace Module\Billing\Services;

use Module\Billing\Models\SubscriptionModel;
use Module\Plan\Models\PlanModel;
use Module\Board\Models\BoardModel;
use Module\Card\Models\CardModel;
use Module\Workspace\Models\WorkspaceMemberModel;

class BillingService
{
    protected SubscriptionModel $model;
    protected PlanModel $planModel;
    protected BoardModel $boardModel;
    protected CardModel $cardModel;
    protected WorkspaceMemberModel $memberModel;

    public function __construct()
    {
        $this->model = new SubscriptionModel();
        $this->planModel = new PlanModel();
        $this->boardModel = new BoardModel();
        $this->cardModel = new CardModel();
        $this->memberModel = new WorkspaceMemberModel();
    }

    public function getPlanLimits(string $planName): ?object
    {
        $plan = $this->planModel->where('name', $planName)->where('isActive', 1)->first();
        if (!$plan) throw new \RuntimeException('Plan not found', 404);

        $features = is_string($plan->features) ? json_decode($plan->features) : $plan->features;
        return (object) [
            'name'        => $plan->name,
            'displayName' => $plan->displayName,
            'price'       => $plan->price,
            'features'    => $features,
        ];
    }

    public function getByWorkspace(int $workspaceId, string $userId): array
    {
        $subscription = $this->model
            ->select('subscriptions.*, plans.name as planName, plans.displayName as planDisplayName')
            ->join('plans', 'plans.name = subscriptions.plan', 'left')
            ->where('subscriptions.workspaceId', $workspaceId)
            ->orderBy('subscriptions.createdAt', 'DESC')
            ->first();

        $plan = $subscription ? $subscription->planName : 'free';
        $workspaceUsage = $this->getWorkspaceUsage($userId);

        return [
            'plan'         => $plan,
            'subscription' => $subscription ? (object)[
                'id'               => $subscription->id,
                'publicId'         => $subscription->publicId ?? '',
                'workspaceId'      => $subscription->workspaceId,
                'plan'             => $subscription->plan,
                'status'           => $subscription->status ?? 'active',
                'startDate'        => $subscription->createdAt,
                'endDate'          => $subscription->currentPeriodEnd ?? null,
                'midtransOrderId'  => $subscription->midtransOrderId ?? null,
                'paymentAmount'    => $subscription->paymentAmount ?? null,
                'createdAt'        => $subscription->createdAt,
                'updatedAt'        => $subscription->updatedAt,
            ] : null,
            'usage'          => $this->getUsage($workspaceId),
            'workspaceUsage' => $workspaceUsage,
        ];
    }

    private function getWorkspaceUsage(string $userId): array
    {
        $db = \Config\Database::connect();
        $count = $db->table('workspace')
            ->where('createdBy', $userId)
            ->where('deletedAt IS NULL', null, false)
            ->countAllResults();
        $limit = 3;
        $plan = $this->planModel->where('name', 'free')->first();
        if ($plan) {
            $limit = (int)$plan->workspaceLimit;
        }
        return ['count' => $count, 'limit' => $limit];
    }

    public function getUsage(int $workspaceId): array
    {
        $boards = $this->boardModel->where('workspaceId', $workspaceId)->where('deletedAt', null)->countAllResults();
        $cards = $this->cardModel
            ->join('list', 'list.id = card.listId')
            ->join('board', 'board.id = list.boardId')
            ->where('board.workspaceId', $workspaceId)
            ->where('card.deletedAt', null)
            ->countAllResults();
        $members = $this->memberModel->where('workspaceId', $workspaceId)->where('deletedAt', null)->countAllResults();

        return [
            'boards'       => $boards,
            'members'      => $members,
            'storageBytes' => 0,
        ];
    }

    public function createCheckout(int $workspaceId, string $userId, string $planName): array
    {
        $plan = $this->planModel->where('name', $planName)->where('isActive', 1)->first();
        if (!$plan) throw new \RuntimeException('Plan not found', 404);

        return [
            'url' => '/billing/checkout?plan=' . $plan->name . '&workspaceId=' . $workspaceId . '&userId=' . $userId,
        ];
    }

    public function handleNotification(array $notification): void
    {
        $type = $notification['type'] ?? '';
        $data = $notification['data'] ?? [];

        if ($type === 'invoice.paid' || $type === 'subscription.updated') {
            $orderId = $data['order_id'] ?? $data['transaction_id'] ?? $data['id'] ?? null;
            if (!$orderId) return;

            $existing = $this->model->where('midtransOrderId', $orderId)->first();

            $payload = [
                'status'             => $data['status'] ?? 'active',
                'currentPeriodStart' => $data['current_period_start'] ?? date('Y-m-d H:i:s'),
                'currentPeriodEnd'   => $data['current_period_end'] ?? date('Y-m-d H:i:s'),
                'updatedAt'          => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->model->update($existing->id, $payload);
            }
        }
    }
}
