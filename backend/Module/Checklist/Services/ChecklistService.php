<?php

namespace Module\Checklist\Services;

use Module\Checklist\Models\CardChecklistModel;
use Module\Checklist\Models\CardChecklistItemModel;

class ChecklistService
{
    protected CardChecklistModel $checklistModel;
    protected CardChecklistItemModel $itemModel;

    public function __construct()
    {
        $this->checklistModel = new CardChecklistModel();
        $this->itemModel = new CardChecklistItemModel();
    }

    public function getByCard(int $cardId): array
    {
        $checklists = $this->checklistModel
            ->where('cardId', $cardId)
            ->where('deletedAt IS NULL')
            ->orderBy('index', 'ASC')
            ->findAll();

        foreach ($checklists as $cl) {
            $cl->items = $this->itemModel
                ->where('checklistId', $cl->id)
                ->where('deletedAt IS NULL')
                ->orderBy('index', 'ASC')
                ->findAll();
        }

        return $checklists;
    }

    public function getChecklistById(string $publicId): object
    {
        $cl = $this->checklistModel->where('publicId', $publicId)->first();
        if (!$cl) throw new \RuntimeException('Checklist not found', 404);
        return $cl;
    }

    public function getItemById(string $publicId): object
    {
        $item = $this->itemModel->where('publicId', $publicId)->first();
        if (!$item) throw new \RuntimeException('Checklist item not found', 404);
        return $item;
    }

    public function createChecklist(array $data): object
    {
        $maxIndex = $this->checklistModel->builder()
            ->selectMax('`index`')
            ->where('cardId', $data['cardId'])
            ->where('deletedAt IS NULL')
            ->get()->getRow()->index ?? -1;

        $now = date('Y-m-d H:i:s');
        $id = $this->checklistModel->insert([
            'publicId'  => generatePublicId(),
            'name'      => $data['name'],
            'index'     => (int)$maxIndex + 1,
            'cardId'    => $data['cardId'],
            'createdBy' => $data['createdBy'] ?? null,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        return $this->checklistModel->find($id);
    }

    public function updateChecklist(string $publicId, array $data): object
    {
        $cl = $this->getChecklistById($publicId);
        $updateData = [];
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        $updateData['updatedAt'] = date('Y-m-d H:i:s');
        $this->checklistModel->update($cl->id, $updateData);
        return $this->checklistModel->find($cl->id);
    }

    public function deleteChecklist(string $publicId): void
    {
        $cl = $this->getChecklistById($publicId);
        $this->checklistModel->delete($cl->id);
    }

    public function createItem(array $data): object
    {
        $maxIndex = $this->itemModel->builder()
            ->selectMax('`index`')
            ->where('checklistId', $data['checklistId'])
            ->where('deletedAt IS NULL')
            ->get()->getRow()->index ?? -1;

        $now = date('Y-m-d H:i:s');
        $id = $this->itemModel->insert([
            'publicId'    => generatePublicId(),
            'title'       => $data['title'],
            'completed'   => 0,
            'index'       => (int)$maxIndex + 1,
            'checklistId' => $data['checklistId'],
            'createdBy'   => $data['createdBy'] ?? null,
            'createdAt'   => $now,
            'updatedAt'   => $now,
        ]);
        return $this->itemModel->find($id);
    }

    public function updateItem(string $publicId, array $data): object
    {
        $item = $this->getItemById($publicId);
        $updateData = [];
        if (isset($data['title'])) $updateData['title'] = $data['title'];
        if (isset($data['completed'])) $updateData['completed'] = $data['completed'] ? 1 : 0;
        $updateData['updatedAt'] = date('Y-m-d H:i:s');
        $this->itemModel->update($item->id, $updateData);
        return $this->itemModel->find($item->id);
    }

    public function deleteItem(string $publicId): void
    {
        $item = $this->getItemById($publicId);
        $this->itemModel->delete($item->id);
    }
}
