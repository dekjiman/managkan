<?php

namespace Module\Board\Services;

use Module\Board\Models\LabelModel;

class LabelService
{
    protected LabelModel $model;

    public function __construct()
    {
        $this->model = new LabelModel();
    }

    public function getByBoard(int $boardId): array
    {
        return $this->model->where('boardId', $boardId)
            ->where('deletedAt', null)
            ->findAll();
    }

    public function getById(string $publicId): object
    {
        $label = $this->model->where('publicId', $publicId)->first();
        if (!$label) {
            throw new \RuntimeException('Label not found', 404);
        }
        return $label;
    }

    public function create(array $data): object
    {
        $now = date('Y-m-d H:i:s');
        $id = $this->model->insert([
            'publicId' => generatePublicId(),
            'name' => $data['name'],
            'colourCode' => $data['colourCode'] ?? null,
            'boardId' => $data['boardId'],
            'createdBy' => $data['createdBy'] ?? null,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);

        return $this->model->find($id);
    }

    public function update(string $publicId, array $data): object
    {
        $label = $this->model->where('publicId', $publicId)->first();
        if (!$label) {
            throw new \RuntimeException('Label not found', 404);
        }

        $allowed = ['name', 'colourCode'];
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
        $this->model->update($label->id, $updateData);

        return $this->model->find($label->id);
    }

    public function delete(string $publicId): void
    {
        $label = $this->model->where('publicId', $publicId)->first();
        if (!$label) {
            throw new \RuntimeException('Label not found', 404);
        }

        $this->model->delete($label->id);
    }
}
