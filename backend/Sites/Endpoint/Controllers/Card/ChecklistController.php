<?php

namespace Sites\Endpoint\Controllers\Card;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Checklist\Services\ChecklistService;

class ChecklistController extends BaseApiController
{
    protected ChecklistService $checklistService;

    public function __construct()
    {
        $this->checklistService = new ChecklistService();
    }

    public function index(?string $cardId = null)
    {
        try {
            $cardId = $cardId ?: $this->request->getGet('cardId');
            if (!$cardId) return $this->fail('Card identifier required', 400);
            $checklists = $this->checklistService->getByCard((int)$cardId);
            return $this->respond($checklists, 200, 'Checklists retrieved');
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
            $input['createdBy'] = $userId;
            $checklist = $this->checklistService->createChecklist($input);
            return $this->respond($checklist, 201, 'Checklist created');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function update(string $publicId)
    {
        try {
            $input = $this->getJsonInput();
            $checklist = $this->checklistService->updateChecklist($publicId, $input);
            return $this->respond($checklist, 200, 'Checklist updated');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function destroy(string $publicId)
    {
        try {
            $this->checklistService->deleteChecklist($publicId);
            return $this->respond(null, 200, 'Checklist deleted');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function addItem(string $checklistId)
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $input = $this->getJsonInput();
            $input['checklistId'] = (int)$checklistId;
            $input['createdBy'] = $userId;
            $item = $this->checklistService->createItem($input);
            return $this->respond($item, 201, 'Checklist item added');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function updateItem(string $publicId)
    {
        try {
            $input = $this->getJsonInput();
            $item = $this->checklistService->updateItem($publicId, $input);
            return $this->respond($item, 200, 'Checklist item updated');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function deleteItem(string $publicId)
    {
        try {
            $this->checklistService->deleteItem($publicId);
            return $this->respond(null, 200, 'Checklist item deleted');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
