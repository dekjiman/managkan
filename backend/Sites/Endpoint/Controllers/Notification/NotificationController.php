<?php

namespace Sites\Endpoint\Controllers\Notification;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Notification\Services\NotificationService;

class NotificationController extends BaseApiController
{
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function index()
    {
        try {
            $userId = $this->getUserId();
            $limit = $this->request->getGet('limit') ?? 20;
            $notifications = $this->notificationService->getByUser($userId, (int) $limit);
            return $this->respond($notifications, 200, 'Notifications retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function unreadCount()
    {
        try {
            $userId = $this->getUserId();
            $count = $this->notificationService->getUnreadCount($userId);
            return $this->respond(['count' => $count], 200, 'Unread count retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function markRead(?string $publicId = null)
    {
        try {
            $publicId = $publicId ?: $this->request->getGet('notificationId');
            if (!$publicId) return $this->fail('Notification identifier required', 400);
            $userId = $this->getUserId();
            $this->notificationService->markRead($publicId, $userId);
            return $this->respondMessage('Notification marked as read');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function markAllRead()
    {
        try {
            $userId = $this->getUserId();
            $this->notificationService->markAllRead($userId);
            return $this->respondMessage('All notifications marked as read');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
