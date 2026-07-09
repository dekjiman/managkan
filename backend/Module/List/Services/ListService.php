<?php

namespace Module\List\Services;

use Module\List\Models\ListModel;
use Module\Board\Models\BoardModel;

class ListService
{
    protected ListModel $model;
    protected BoardModel $boardModel;

    public function __construct()
    {
        $this->model = new ListModel();
        $this->boardModel = new BoardModel();
    }

    public function getByBoard(string $boardPublicId): array
    {
        $board = $this->boardModel->where('publicId', $boardPublicId)->first();
        if (!$board) {
            throw new \RuntimeException('Board not found', 404);
        }

        return $this->model->where('boardId', $board->id)
            ->where('deletedAt IS NULL')
            ->orderBy('index', 'ASC')
            ->findAll();
    }

    public function getById(string $publicId): object
    {
        $list = $this->model->where('publicId', $publicId)->first();
        if (!$list) {
            throw new \RuntimeException('List not found', 404);
        }
        return $list;
    }

    public function create(array $data, string $userId): object
    {
        $board = $this->boardModel->where('publicId', $data['boardPublicId'])->first();
        if (!$board) {
            throw new \RuntimeException('Board not found', 404);
        }

        $maxIndex = $this->model->builder()
            ->selectMax('`index`')
            ->where('boardId', $board->id)
            ->where('deletedAt IS NULL')
            ->get()
            ->getRow()
            ->index ?? -1;

        $now = date('Y-m-d H:i:s');
        $id = $this->model->insert([
            'publicId'  => generatePublicId(),
            'name'      => $data['name'],
            'index'     => (int)$maxIndex + 1,
            'boardId'   => $board->id,
            'createdBy' => $userId,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);

        return $this->model->find($id);
    }

    public function update(string $publicId, array $data): object
    {
        $list = $this->model->where('publicId', $publicId)->first();
        if (!$list) {
            throw new \RuntimeException('List not found', 404);
        }

        $allowed = ['name'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            throw new \RuntimeException('No valid fields to update', 400);
        }

        $updateData['updatedAt'] = date('Y-m-d H:i:s');
        $this->model->update($list->id, $updateData);

        return $this->model->find($list->id);
    }

    public function reorder(string $boardPublicId, array $listIds): void
    {
        $board = $this->boardModel->where('publicId', $boardPublicId)->first();
        if (!$board) {
            throw new \RuntimeException('Board not found', 404);
        }

        foreach ($listIds as $index => $publicId) {
            $this->model->builder()
                ->where('publicId', $publicId)
                ->where('boardId', $board->id)
                ->update(['index' => (int)$index, 'updatedAt' => date('Y-m-d H:i:s')]);
        }
    }

    public function delete(string $publicId): void
    {
        $list = $this->model->where('publicId', $publicId)->first();
        if (!$list) {
            throw new \RuntimeException('List not found', 404);
        }
        $this->model->delete($list->id);
    }
}
