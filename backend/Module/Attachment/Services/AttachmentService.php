<?php

namespace Module\Attachment\Services;

use Module\Attachment\Models\AttachmentModel;

class AttachmentService
{
    protected AttachmentModel $model;

    public function __construct()
    {
        $this->model = new AttachmentModel();
        helper('storage');
    }

    public function format(object $a): object
    {
        $a->downloadUrl = base_url("api/attachments/download/{$a->publicId}");
        $a->path = $a->downloadUrl;
        return $a;
    }

    public function getByCard(int $cardId): array
    {
        $attachments = $this->model
            ->where('cardId', $cardId)
            ->where('deletedAt IS NULL')
            ->findAll();

        return array_map(fn($a) => $this->format($a), $attachments);
    }

    public function getById(string $publicId): object
    {
        $attachment = $this->model->where('publicId', $publicId)->first();
        if (!$attachment) throw new \RuntimeException('Attachment not found', 404);
        return $attachment;
    }

    public function getByIdFormatted(string $publicId): object
    {
        return $this->format($this->getById($publicId));
    }

    public function create(array $data, string $userId): object
    {
        $now = date('Y-m-d H:i:s');
        $id = $this->model->insert([
            'publicId'         => generatePublicId(),
            'cardId'           => $data['cardId'],
            'filename'         => $data['filename'],
            'originalFilename' => $data['originalFilename'],
            'contentType'      => $data['contentType'],
            'size'             => $data['size'],
            'path'             => $data['path'],
            'createdBy'        => $userId,
            'createdAt'        => $now,
        ]);
        return $this->format($this->model->find($id));
    }

    public function delete(string $publicId): void
    {
        $attachment = $this->model->where('publicId', $publicId)->first();
        if (!$attachment) throw new \RuntimeException('Attachment not found', 404);
        deleteFromStorage($attachment->path);
        $this->model->update($attachment->id, ['deletedAt' => date('Y-m-d H:i:s')]);
    }
}
