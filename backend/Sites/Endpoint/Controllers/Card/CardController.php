<?php

namespace Sites\Endpoint\Controllers\Card;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Card\Services\CardService;

class CardController extends BaseApiController
{
    protected CardService $cardService;

    public function __construct()
    {
        $this->cardService = new CardService();
    }

    public function index(?string $listPublicId = null)
    {
        try {
            $listPublicId = $listPublicId ?: $this->request->getGet('listPublicId');
            if (!$listPublicId) return $this->fail('List identifier required', 400);
            $cards = $this->cardService->getByList($listPublicId);
            return $this->respond($cards, 200, 'Cards retrieved');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function show(string $publicId)
    {
        try {
            $card = $this->cardService->getById($publicId);
            return $this->respond($card, 200, 'Card retrieved');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
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
            $card = $this->cardService->create($input, $userId);
            return $this->respond($card, 201, 'Card created');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function update(string $publicId)
    {
        try {
            $input = $this->getJsonInput();
            $card = $this->cardService->update($publicId, $input);
            return $this->respond($card, 200, 'Card updated');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function move(string $publicId)
    {
        try {
            $input = $this->getJsonInput();
            $listPublicId = $input['listPublicId'] ?? '';
            $index = (int)($input['index'] ?? 0);

            if (empty($listPublicId)) {
                return $this->fail('listPublicId is required', 400);
            }

            $card = $this->cardService->move($publicId, $listPublicId, $index);
            return $this->respond($card, 200, 'Card moved');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function duplicate(string $publicId)
    {
        try {
            $userId = $this->getUserId();
            if (!$userId) {
                return $this->fail('Unauthorized', 401);
            }

            $card = $this->cardService->duplicate($publicId, $userId);
            return $this->respond($card, 201, 'Card duplicated');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function destroy(string $publicId)
    {
        try {
            $this->cardService->delete($publicId);
            return $this->respond(null, 200, 'Card deleted');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function toggleLabel(string $cardPublicId, string $labelId)
    {
        try {
            $label = $this->cardService->toggleLabel($cardPublicId, $labelId);
            return $this->respond($label, 200);
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function toggleMember(string $cardPublicId, string $memberId)
    {
        try {
            $result = $this->cardService->toggleMember($cardPublicId, $memberId);
            return $this->respond($result, 200);
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }
}
