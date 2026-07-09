<?php

namespace Sites\Endpoint\Controllers\Card;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Attachment\Services\AttachmentService;
use Module\Card\Models\CardModel;

class AttachmentController extends BaseApiController
{
    protected AttachmentService $attachmentService;
    protected CardModel $cardModel;

    public function __construct()
    {
        $this->attachmentService = new AttachmentService();
        $this->cardModel = new CardModel();
    }

    private function resolveCardId(string $id): int
    {
        $card = $this->cardModel->where('publicId', $id)->orWhere('id', $id)->first();
        if (!$card) throw new \RuntimeException('Card not found', 404);
        return $card->id;
    }

    public function upload(?string $cardId = null)
    {
        try {
            $cardId = $cardId ?: $this->request->getGet('cardId');
            if (!$cardId) return $this->fail('Card identifier required', 400);
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $file = $this->request->getFile('file');
            if (!$file || !$file->isValid()) {
                $error = $file ? $file->getError() : UPLOAD_ERR_NO_FILE;
                $message = match ($error) {
                    UPLOAD_ERR_INI_SIZE   => 'File exceeds PHP upload_max_filesize limit',
                    UPLOAD_ERR_FORM_SIZE  => 'File exceeds maximum allowed size (50MB)',
                    UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary directory',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension',
                    default               => 'No valid file uploaded',
                };
                return $this->fail($message, 400);
            }

            $maxSize = 50 * 1024 * 1024;
            if ($file->getSize() > $maxSize) {
                return $this->fail('File exceeds maximum size of 50MB', 400);
            }

            helper('storage');
            $publicId = generatePublicId();
            $ext = $file->getExtension();
            $storedName = $publicId . ($ext ? '.' . $ext : '');
            $path = uploadToStorage(
                file_get_contents($file->getTempName()),
                $storedName,
                $file->getMimeType()
            );

            $data = [
                'cardId'           => $this->resolveCardId($cardId),
                'filename'         => $storedName,
                'originalFilename' => $file->getClientName(),
                'contentType'      => $file->getMimeType(),
                'size'             => $file->getSize(),
                'path'             => $path,
            ];

            $attachment = $this->attachmentService->create($data, $userId);
            return $this->respond($attachment, 201, 'Attachment uploaded');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function index(?string $cardId = null)
    {
        try {
            $cardId = $cardId ?: $this->request->getGet('cardId');
            if (!$cardId) return $this->fail('Card identifier required', 400);
            $attachments = $this->attachmentService->getByCard($this->resolveCardId($cardId));
            return $this->respond($attachments, 200, 'Attachments retrieved');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function download(string $publicId)
    {
        try {
            $attachment = $this->attachmentService->getById($publicId);
            helper('storage');
            $filePath = getStoragePath($attachment->path);
            if (!file_exists($filePath)) {
                return $this->fail('File not found on storage', 404);
            }

            $disposition = 'inline';
            $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            if (!in_array($attachment->contentType, $imageTypes)) {
                $disposition = 'attachment';
            }

            return $this->response
                ->setHeader('Content-Type', $attachment->contentType)
                ->setHeader('Content-Disposition', $disposition . '; filename="' . $attachment->originalFilename . '"')
                ->setHeader('Content-Length', (string)$attachment->size)
                ->setBody(file_get_contents($filePath));
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function destroy(string $publicId)
    {
        try {
            $this->attachmentService->delete($publicId);
            return $this->respond(null, 200, 'Attachment deleted');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
