<?php

namespace Sites\Endpoint\Controllers\Card;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Activity\Services\ActivityService;
use Module\Card\Models\CardModel;

class ActivityController extends BaseApiController
{
    protected ActivityService $activityService;
    protected CardModel $cardModel;

    public function __construct()
    {
        $this->activityService = new ActivityService();
        $this->cardModel = new CardModel();
    }

    private function resolveCardId(string $id): int
    {
        $card = $this->cardModel->where('publicId', $id)->orWhere('id', $id)->first();
        if (!$card) throw new \RuntimeException('Card not found', 404);
        return $card->id;
    }

    public function index(?string $cardId = null)
    {
        try {
            $cardId = $cardId ?: $this->request->getGet('cardId');
            if (!$cardId) return $this->fail('Card identifier required', 400);
            $activities = $this->activityService->getByCard($this->resolveCardId($cardId));
            return $this->respond($activities, 200, 'Activities retrieved');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
