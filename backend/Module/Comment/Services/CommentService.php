<?php

namespace Module\Comment\Services;

use Module\Comment\Models\CommentModel;
use Module\Activity\Models\ActivityModel;
use Module\Notification\Services\NotificationService;

class CommentService
{
    protected CommentModel $model;
    protected ActivityModel $activityModel;
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->model = new CommentModel();
        $this->activityModel = new ActivityModel();
        $this->notificationService = new NotificationService();
    }

    public function getByCard(int $cardId): array
    {
        return $this->model
            ->where('cardId', $cardId)
            ->where('deletedAt IS NULL')
            ->orderBy('createdAt', 'ASC')
            ->findAll();
    }

    public function getById(string $publicId): object
    {
        $comment = $this->model->where('publicId', $publicId)->first();
        if (!$comment) throw new \RuntimeException('Comment not found', 404);
        return $comment;
    }

    public function create(array $data, string $userId): object
    {
        $now = date('Y-m-d H:i:s');
        $publicId = generatePublicId();
        $id = $this->model->insert([
            'publicId'  => $publicId,
            'comment'   => $data['comment'],
            'cardId'    => $data['cardId'],
            'createdBy' => $userId,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);

        $this->activityModel->insert([
            'publicId'  => generatePublicId(),
            'type'      => 'card.updated.comment.added',
            'cardId'    => $data['cardId'],
            'commentId' => $id,
            'createdBy' => $userId,
            'createdAt' => $now,
        ]);

        $mentionedUserIds = $data['mentionedUserIds'] ?? [];
        if (!empty($mentionedUserIds)) {
            $cardInfo = $this->getCardInfoById($data['cardId']);

            foreach ($mentionedUserIds as $mentionedUserId) {
                if ($mentionedUserId === $userId) continue;

                $this->notificationService->create([
                    'type'        => 'comment_mentioned',
                    'title'       => 'You were mentioned in a comment',
                    'data'        => [
                        'comment'      => $data['comment'],
                        'cardId'       => $data['cardId'],
                        'cardPublicId' => $cardInfo['cardPublicId'] ?? null,
                        'commentId'    => $publicId,
                        'workspaceSlug' => $cardInfo['workspaceSlug'] ?? null,
                        'boardSlug'    => $cardInfo['boardSlug'] ?? null,
                    ],
                    'userId'      => $mentionedUserId,
                    'workspaceId' => $cardInfo['workspaceId'] ?? null,
                    'createdBy'   => $userId,
                ]);
            }
        }

        return $this->model->find($id);
    }

    private function getCardInfoById(int $cardId): array
    {
        $db = \Config\Database::connect();
        $row = $db->table('card')
            ->select('card.publicId as cardPublicId, board.workspaceId, workspace.slug as workspaceSlug, board.slug as boardSlug')
            ->join('list', 'list.id = card.listId')
            ->join('board', 'board.id = list.boardId')
            ->join('workspace', 'workspace.id = board.workspaceId')
            ->where('card.id', $cardId)
            ->get()
            ->getFirstRow();
        return $row ? (array) $row : [];
    }

    public function update(string $publicId, array $data, string $userId): object
    {
        $comment = $this->getById($publicId);
        if ($comment->createdBy !== $userId) {
            throw new \RuntimeException('You can only edit your own comments', 403);
        }
        $this->model->update($comment->id, [
            'comment'   => $data['comment'],
            'updatedAt' => date('Y-m-d H:i:s'),
        ]);

        $this->activityModel->insert([
            'publicId'    => generatePublicId(),
            'type'        => 'card.updated.comment.updated',
            'cardId'      => $comment->cardId,
            'commentId'   => $comment->id,
            'fromComment' => $comment->comment,
            'toComment'   => $data['comment'],
            'createdBy'   => $userId,
            'createdAt'   => date('Y-m-d H:i:s'),
        ]);

        return $this->model->find($comment->id);
    }

    public function delete(string $publicId, string $userId): void
    {
        $comment = $this->getById($publicId);
        if ($comment->createdBy !== $userId) {
            throw new \RuntimeException('You can only delete your own comments', 403);
        }

        $this->activityModel->insert([
            'publicId'  => generatePublicId(),
            'type'      => 'card.updated.comment.deleted',
            'cardId'    => $comment->cardId,
            'commentId' => $comment->id,
            'createdBy' => $userId,
            'createdAt' => date('Y-m-d H:i:s'),
        ]);

        $this->model->delete($comment->id);
    }
}
