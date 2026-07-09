<?php

namespace Module\Activity\Services;

use Module\Activity\Models\ActivityModel;

class ActivityService
{
    protected ActivityModel $model;

    public function __construct()
    {
        $this->model = new ActivityModel();
    }

    public function getByCard(int $cardId): array
    {
        $activities = $this->model
            ->where('cardId', $cardId)
            ->orderBy('createdAt', 'DESC')
            ->findAll();

        $db = \Config\Database::connect();

        $cardRow = $db->table('card')->where('id', $cardId)->get()->getFirstRow();
        $cardInfo = $cardRow ? (object)[
            'id'   => (int)$cardRow->id,
            'name' => $cardRow->title,
        ] : null;

        foreach ($activities as $a) {
            $a->userName = null;
            $a->userImage = null;
            $a->commentText = null;

            if ($a->createdBy) {
                $row = $db->table('user')->where('id', $a->createdBy)->get()->getFirstRow();
                if ($row) {
                    $a->userName = $row->name ?? null;
                    $a->userImage = $row->image ?? null;
                }
            }

            if ($a->type === 'card.updated.comment.added' && $a->commentId) {
                $comment = $db->table('card_comments')->where('id', $a->commentId)->get()->getFirstRow();
                $a->commentText = $comment->comment ?? null;
            }
        }

        return $activities;
    }

    public function create(array $data): object
    {
        $now = date('Y-m-d H:i:s');
        $id = $this->model->insert(array_merge($data, [
            'publicId'  => generatePublicId(),
            'createdAt' => $now,
        ]));
        return $this->model->find($id);
    }
}
