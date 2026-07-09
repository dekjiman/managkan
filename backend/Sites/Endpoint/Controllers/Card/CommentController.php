<?php

namespace Sites\Endpoint\Controllers\Card;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Comment\Services\CommentService;
use Module\Card\Models\CardModel;

class CommentController extends BaseApiController
{
    protected CommentService $commentService;
    protected CardModel $cardModel;

    public function __construct()
    {
        $this->commentService = new CommentService();
        $this->cardModel = new CardModel();
    }

    private function resolveCardId(string $id): int
    {
        $card = $this->cardModel->where('publicId', $id)->orWhere('id', $id)->first();
        if (!$card) throw new \RuntimeException('Card not found', 404);
        return $card->id;
    }

    private function attachUser(object $comment): object
    {
        if ($comment->createdBy) {
            $db = \Config\Database::connect();
            $row = $db->table('user')->where('id', $comment->createdBy)->get()->getFirstRow();
            $comment->userName = $row->name ?? null;
            $comment->userImage = $row->image ?? null;
        } else {
            $comment->userName = null;
            $comment->userImage = null;
        }
        return $comment;
    }

    public function index(?string $cardId = null)
    {
        try {
            $cardId = $cardId ?: $this->request->getGet('cardId');
            if (!$cardId) return $this->fail('Card identifier required', 400);
            $comments = $this->commentService->getByCard($this->resolveCardId($cardId));
            foreach ($comments as $c) {
                $this->attachUser($c);
            }
            return $this->respond($comments, 200, 'Comments retrieved');
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
            if (empty($input['cardId'])) return $this->fail('cardId is required', 400);
            $input['cardId'] = $this->resolveCardId($input['cardId']);
            $comment = $this->commentService->create($input, $userId);
            $this->attachUser($comment);
            return $this->respond($comment, 201, 'Comment created');
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
            $comment = $this->commentService->update($publicId, $input, $userId);
            $this->attachUser($comment);
            return $this->respond($comment, 200, 'Comment updated');
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

            $this->commentService->delete($publicId, $userId);
            return $this->respond(null, 200, 'Comment deleted');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
