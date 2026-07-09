<?php

namespace Module\Notification\Services;

use Module\Notification\Models\NotificationModel;

class NotificationService
{
    protected NotificationModel $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    public function create(array $data): void
    {
        $this->model->insert([
            'publicId'    => generatePublicId(),
            'type'        => $data['type'],
            'title'       => $data['title'] ?? $this->getDefaultTitle($data['type']),
            'data'        => json_encode($data['data'] ?? []),
            'userId'      => $data['userId'],
            'workspaceId' => $data['workspaceId'] ?? null,
            'createdBy'   => $data['createdBy'] ?? null,
            'createdAt'   => date('Y-m-d H:i:s'),
        ]);
    }

    private function getDefaultTitle(string $type): string
    {
        $titles = [
            'member_added' => 'You have been added to a workspace',
            'card_assigned' => 'You have been assigned to a card',
        ];
        return $titles[$type] ?? 'New notification';
    }

    public function getByUser(string $userId, int $limit = 20): array
    {
        return $this->model
            ->select('publicId, type, title, data, read, createdAt')
            ->where('userId', $userId)
            ->orderBy('createdAt', 'DESC')
            ->findAll($limit);
    }

    public function getUnreadCount(string $userId): int
    {
        return $this->model
            ->where('userId', $userId)
            ->where('read', 0)
            ->countAllResults();
    }

    public function markRead(string $publicId, string $userId): void
    {
        $notification = $this->model->where('publicId', $publicId)->where('userId', $userId)->first();
        if (!$notification) throw new \RuntimeException('Notification not found', 404);

        $this->model->update($notification->id, [
            'read' => 1,
        ]);
    }

    public function markAllRead(string $userId): void
    {
        $this->model->builder()
            ->where('userId', $userId)
            ->where('read', 0)
            ->set(['read' => 1])
            ->update();
    }
}
