<?php

namespace Sites\Endpoint\Controllers\Board;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Board\Services\BoardService;

class BoardController extends BaseApiController
{
    protected BoardService $boardService;

    public function __construct()
    {
        $this->boardService = new BoardService();
    }

    public function index(string $wsPublicId)
    {
        try {
            $type = $this->request->getGet('type');
            $boards = $this->boardService->getByWorkspace($wsPublicId, $type);
            return $this->respond($boards, 200, 'Boards retrieved');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function show(string $publicId)
    {
        try {
            $board = $this->boardService->getById($publicId);
            return $this->respond($board, 200, 'Board retrieved');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function store(string $wsPublicId)
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $input = $this->getJsonInput();
            $board = $this->boardService->create($wsPublicId, $input, $userId);
            return $this->respond($board, 201, 'Board created');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function update(string $publicId)
    {
        try {
            $input = $this->getJsonInput();
            $board = $this->boardService->update($publicId, $input);
            return $this->respond($board, 200, 'Board updated');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function destroy(string $publicId)
    {
        try {
            $this->boardService->delete($publicId);
            return $this->respond(null, 200, 'Board deleted');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function checkSlugAvailability()
    {
        try {
            $slug = $this->request->getGet('slug');

            if (empty($slug)) {
                return $this->fail('Slug parameter is required', 400);
            }

            $result = $this->boardService->checkSlugAvailability($slug);
            return $this->respond($result, 200, 'Slug availability checked');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
