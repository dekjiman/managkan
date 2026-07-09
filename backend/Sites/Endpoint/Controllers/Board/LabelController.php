<?php

namespace Sites\Endpoint\Controllers\Board;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Board\Services\LabelService;
use Module\Board\Models\BoardModel;

class LabelController extends BaseApiController
{
    protected LabelService $labelService;
    protected BoardModel $boardModel;

    public function __construct()
    {
        $this->labelService = new LabelService();
        $this->boardModel = new BoardModel();
    }

    public function index(?string $boardId = null)
    {
        try {
            $boardId = $boardId ?: $this->request->getGet('boardPublicId');
            if (!$boardId) return $this->fail('Board identifier required', 400);
            $board = $this->boardModel
                ->where('publicId', $boardId)
                ->orWhere('slug', $boardId)
                ->first();
            if (!$board) return $this->fail('Board not found', 404);
            $labels = $this->labelService->getByBoard((int)$board->id);
            return $this->respond($labels, 200, 'Labels retrieved');
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
            $board = $this->boardModel
                ->where('publicId', $input['boardPublicId'] ?? '')
                ->orWhere('slug', $input['boardPublicId'] ?? '')
                ->first();
            if (!$board) return $this->fail('Board not found', 404);

            $input['boardId'] = $board->id;
            $input['createdBy'] = $userId;
            $label = $this->labelService->create($input);
            return $this->respond($label, 201, 'Label created');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function update(string $publicId)
    {
        try {
            $input = $this->getJsonInput();
            $label = $this->labelService->update($publicId, $input);
            return $this->respond($label, 200, 'Label updated');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function destroy(string $publicId)
    {
        try {
            $this->labelService->delete($publicId);
            return $this->respond(null, 200, 'Label deleted');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
