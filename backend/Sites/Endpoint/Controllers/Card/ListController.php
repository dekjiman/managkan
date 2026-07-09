<?php

namespace Sites\Endpoint\Controllers\Card;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\List\Services\ListService;

class ListController extends BaseApiController
{
    protected ListService $listService;
    protected \Module\Board\Models\BoardModel $boardModel;

    public function __construct()
    {
        $this->listService = new ListService();
        $this->boardModel = new \Module\Board\Models\BoardModel();
    }

    public function index(?string $boardPublicId = null)
    {
        try {
            $boardPublicId = $boardPublicId ?: $this->request->getGet('boardPublicId');
            if (!$boardPublicId) return $this->fail('Board identifier required', 400);
            $lists = $this->listService->getByBoard($boardPublicId);
            return $this->respond($lists, 200, 'Lists retrieved');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function show(string $publicId)
    {
        try {
            $list = $this->listService->getById($publicId);
            return $this->respond($list, 200, 'List retrieved');
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
            $list = $this->listService->create($input, $userId);
            return $this->respond($list, 201, 'List created');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function update(string $publicId)
    {
        try {
            $input = $this->getJsonInput();
            $list = $this->listService->update($publicId, $input);
            return $this->respond($list, 200, 'List updated');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function reorder()
    {
        try {
            $input = $this->getJsonInput();
            $boardPublicId = $input['boardPublicId'] ?? '';
            $listIds = $input['listIds'] ?? [];

            if (empty($boardPublicId) || empty($listIds)) {
                return $this->fail('boardPublicId and listIds are required', 400);
            }

            $this->listService->reorder($boardPublicId, $listIds);
            return $this->respond(null, 200, 'Lists reordered');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function destroy(string $publicId)
    {
        try {
            $this->listService->delete($publicId);
            return $this->respond(null, 200, 'List deleted');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
