<?php

namespace Module\Card\Services;

use Module\Card\Models\CardModel;
use Module\Card\Models\CardLabelModel;
use Module\Card\Models\CardMemberModel;
use Module\List\Models\ListModel;
use Module\Board\Models\LabelModel;
use Module\Workspace\Models\WorkspaceMemberModel;
use Module\Checklist\Models\CardChecklistModel;
use Module\Checklist\Models\CardChecklistItemModel;
use Module\Comment\Models\CommentModel;
use Module\Attachment\Models\AttachmentModel;
use Module\Notification\Services\NotificationService;

class CardService
{
    protected CardModel $model;
    protected CardLabelModel $cardLabelModel;
    protected CardMemberModel $cardMemberModel;
    protected ListModel $listModel;
    protected LabelModel $labelModel;
    protected WorkspaceMemberModel $workspaceMemberModel;
    protected CardChecklistModel $checklistModel;
    protected CardChecklistItemModel $checklistItemModel;
    protected CommentModel $commentModel;
    protected AttachmentModel $attachmentModel;
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->model = new CardModel();
        $this->cardLabelModel = new CardLabelModel();
        $this->cardMemberModel = new CardMemberModel();
        $this->listModel = new ListModel();
        $this->labelModel = new LabelModel();
        $this->workspaceMemberModel = new WorkspaceMemberModel();
        $this->checklistModel = new CardChecklistModel();
        $this->checklistItemModel = new CardChecklistItemModel();
        $this->commentModel = new CommentModel();
        $this->attachmentModel = new AttachmentModel();
        $this->notificationService = new NotificationService();
    }

    public function getByList(string $listPublicId): array
    {
        $list = $this->listModel->where('publicId', $listPublicId)->first();
        if (!$list) {
            throw new \RuntimeException('List not found', 404);
        }

        $cards = $this->model->where('listId', $list->id)
            ->where('deletedAt IS NULL')
            ->orderBy('index', 'ASC')
            ->findAll();

        return $this->attachLabelsAndMembers($cards);
    }

    public function getById(string $publicId): object
    {
        $card = $this->model->where('publicId', $publicId)->first();
        if (!$card) {
            throw new \RuntimeException('Card not found', 404);
        }

        $card = $this->attachFullRelations($card);
        return $card;
    }

    private function attachLabelsAndMembers(array $cards): array
    {
        if (empty($cards)) return [];

        $cardIds = array_map(fn($c) => $c->id, $cards);
        $labelsMap = [];
        $membersMap = [];

        $clRaw = $this->cardLabelModel->builder()
            ->whereIn('cardId', $cardIds)
            ->get()->getResultObject();
        $labelIds = array_unique(array_map(fn($cl) => $cl->labelId, $clRaw));
        $labelObjects = [];
        if (!empty($labelIds)) {
            $labelRows = $this->labelModel->builder()->whereIn('id', $labelIds)->get()->getResultObject();
            foreach ($labelRows as $lb) {
                $labelObjects[$lb->id] = $lb;
            }
        }
        foreach ($clRaw as $cl) {
            $label = $labelObjects[$cl->labelId] ?? null;
            if ($label) {
                $labelsMap[$cl->cardId][] = (object)[
                    'cardId'     => (int)$cl->cardId,
                    'labelId'    => (int)$cl->labelId,
                    'id'         => (int)$label->id,
                    'publicId'   => $label->publicId,
                    'name'       => $label->name,
                    'colourCode' => $label->colourCode,
                ];
            }
        }

        $cmRaw = $this->cardMemberModel->builder()
            ->whereIn('cardId', $cardIds)
            ->get()->getResultObject();
        $memberIds = array_unique(array_map(fn($cm) => $cm->workspaceMemberId, $cmRaw));
        $memberObjects = [];
        if (!empty($memberIds)) {
            $memberRows = $this->workspaceMemberModel->builder()
                ->whereIn('id', $memberIds)
                ->get()->getResultObject();
            foreach ($memberRows as $wm) {
                $memberObjects[$wm->id] = $wm;
            }
        }
        foreach ($cmRaw as $cm) {
            $wm = $memberObjects[$cm->workspaceMemberId] ?? null;
            if ($wm) {
                $userName = null;
                $userImage = null;
                if ($wm->userId) {
                    $user = $this->getUser($wm->userId);
                    $userName = $user->name ?? null;
                    $userImage = $user->image ?? null;
                }
                $membersMap[$cm->cardId][] = (object)[
                    'cardId'             => (int)$cm->cardId,
                    'workspaceMemberId'  => (int)$cm->workspaceMemberId,
                    'id'                 => (int)$wm->id,
                    'publicId'           => $wm->publicId,
                    'userId'             => $wm->userId,
                    'role'               => $wm->role,
                    'userName'           => $userName,
                    'userImage'          => $userImage,
                ];
            }
        }

        foreach ($cards as $card) {
            $card->labels = $labelsMap[$card->id] ?? [];
            $card->members = $membersMap[$card->id] ?? [];
        }

        return $cards;
    }

    private function attachFullRelations(object $card): object
    {
        $list = $this->listModel->where('id', $card->listId)->first();
        $card->list = $list ? (object)[
            'id'        => (int)$list->id,
            'publicId'  => $list->publicId,
            'name'      => $list->name,
            'index'     => (int)$list->index,
            'boardId'   => (int)$list->boardId,
            'createdBy' => $list->createdBy,
            'createdAt' => $list->createdAt,
            'updatedAt' => $list->updatedAt,
            'deletedAt' => $list->deletedAt,
            'deletedBy' => $list->deletedBy,
        ] : null;

        $card->labels = [];
        $clRaw = $this->cardLabelModel->builder()
            ->where('cardId', $card->id)
            ->get()->getResultObject();

        if (!empty($clRaw)) {
            $labelIds = array_unique(array_map(fn($cl) => $cl->labelId, $clRaw));
            $labelRows = $this->labelModel->builder()->whereIn('id', $labelIds)->get()->getResultObject();
            $labelMap = [];
            foreach ($labelRows as $lb) {
                $labelMap[$lb->id] = $lb;
            }
            foreach ($clRaw as $cl) {
                $label = $labelMap[$cl->labelId] ?? null;
                if ($label) {
                    $card->labels[] = (object)[
                        'cardId'     => (int)$cl->cardId,
                        'labelId'    => (int)$cl->labelId,
                        'id'         => (int)$label->id,
                        'publicId'   => $label->publicId,
                        'name'       => $label->name,
                        'colourCode' => $label->colourCode,
                    ];
                }
            }
        }

        $card->members = [];
        $cmRaw = $this->cardMemberModel->builder()
            ->where('cardId', $card->id)
            ->get()->getResultObject();

        if (!empty($cmRaw)) {
            $wmIds = array_unique(array_map(fn($cm) => $cm->workspaceMemberId, $cmRaw));
            $wmRows = $this->workspaceMemberModel->builder()->whereIn('id', $wmIds)->get()->getResultObject();
            $wmMap = [];
            foreach ($wmRows as $wm) {
                $wmMap[$wm->id] = $wm;
            }
            foreach ($cmRaw as $cm) {
                $wm = $wmMap[$cm->workspaceMemberId] ?? null;
                if ($wm) {
                    $userName = null;
                    $userImage = null;
                    if ($wm->userId) {
                        $user = $this->getUser($wm->userId);
                        $userName = $user->name ?? null;
                        $userImage = $user->image ?? null;
                    }
                    $card->members[] = (object)[
                        'cardId'             => (int)$cm->cardId,
                        'workspaceMemberId'  => (int)$cm->workspaceMemberId,
                        'id'                 => (int)$wm->id,
                        'publicId'           => $wm->publicId,
                        'userId'             => $wm->userId,
                        'role'               => $wm->role,
                        'userName'           => $userName,
                        'userImage'          => $userImage,
                    ];
                }
            }
        }

        $checklists = $this->checklistModel
            ->where('cardId', $card->id)
            ->where('deletedAt IS NULL')
            ->orderBy('index', 'ASC')
            ->findAll();
        foreach ($checklists as $cl) {
            $items = $this->checklistItemModel
                ->where('checklistId', $cl->id)
                ->where('deletedAt IS NULL')
                ->orderBy('index', 'ASC')
                ->findAll();
            $cl->items = $items;
        }
        $card->checklists = $checklists;

        $comments = $this->commentModel
            ->where('cardId', $card->id)
            ->where('deletedAt IS NULL')
            ->orderBy('createdAt', 'ASC')
            ->findAll();
        foreach ($comments as $comment) {
            $user = $this->getUser($comment->createdBy);
            $comment->user = $user ? (object)[
                'id'    => $user->id,
                'name'  => $user->name ?? null,
                'email' => $user->email,
                'image' => $user->image ?? null,
            ] : null;
        }
        $card->comments = $comments;

        $attachments = $this->attachmentModel
            ->where('cardId', $card->id)
            ->where('deletedAt IS NULL')
            ->findAll();
        $attSvc = new \Module\Attachment\Services\AttachmentService();
        $card->attachments = array_map(fn($a) => $attSvc->format($a), $attachments);

        return $card;
    }

    private function getUser(string $userId): ?object
    {
        $db = \Config\Database::connect();
        $row = $db->table('user')->where('id', $userId)->get()->getFirstRow();
        return $row ?: null;
    }

    public function create(array $data, string $userId): object
    {
        $list = $this->listModel->where('publicId', $data['listPublicId'])->first();
        if (!$list) {
            throw new \RuntimeException('List not found', 404);
        }

        $maxIndex = $this->model->builder()
            ->selectMax('`index`')
            ->where('listId', $list->id)
            ->where('deletedAt IS NULL')
            ->get()
            ->getRow()
            ->index ?? -1;

        $now = date('Y-m-d H:i:s');
        $cardId = $this->model->insert([
            'publicId'    => generatePublicId(),
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'index'       => (int)$maxIndex + 1,
            'listId'      => $list->id,
            'cardNumber'  => $data['cardNumber'] ?? null,
            'dueDate'     => $data['dueDate'] ?? null,
            'createdBy'   => $userId,
            'createdAt'   => $now,
            'updatedAt'   => $now,
        ]);

        if (isset($data['labelPublicIds'])) {
            foreach ($data['labelPublicIds'] as $labelPublicId) {
                $label = $this->labelModel->where('publicId', $labelPublicId)->first();
                if ($label) {
                    $this->cardLabelModel->builder()->insert(['cardId' => $cardId, 'labelId' => $label->id]);
                }
            }
        }

        if (isset($data['memberPublicIds'])) {
            $workspaceId = $this->getWorkspaceIdByList($list->id);
            $cardInfo = $this->getCardNavigationInfo($list->id);
            $cardPublicId = $this->model->find($cardId)->publicId;
            foreach ($data['memberPublicIds'] as $memberPublicId) {
                $member = $this->workspaceMemberModel->where('publicId', $memberPublicId)->first();
                if ($member) {
                    $this->cardMemberModel->builder()->insert(['cardId' => $cardId, 'workspaceMemberId' => $member->id]);

                    if ($member->userId && $member->userId !== $userId) {
                        $this->notificationService->create([
                            'type'        => 'card_assigned',
                            'title'       => 'You have been assigned to a card',
                            'data'        => [
                                'cardTitle'     => $data['title'],
                                'cardPublicId'  => $cardPublicId,
                                'workspaceSlug' => $cardInfo['workspaceSlug'] ?? null,
                                'boardSlug'     => $cardInfo['boardSlug'] ?? null,
                            ],
                            'userId'      => $member->userId,
                            'workspaceId' => $workspaceId,
                            'createdBy'   => $userId,
                        ]);
                    }
                }
            }
        }

        return $this->model->find($cardId);
    }

    public function update(string $publicId, array $data): object
    {
        $card = $this->model->where('publicId', $publicId)->first();
        if (!$card) {
            throw new \RuntimeException('Card not found', 404);
        }

        $allowed = ['title', 'description', 'dueDate'];
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
        $this->model->update($card->id, $updateData);

        return $this->model->find($card->id);
    }

    public function move(string $publicId, string $listPublicId, int $index): object
    {
        $card = $this->model->where('publicId', $publicId)->first();
        if (!$card) {
            throw new \RuntimeException('Card not found', 404);
        }

        $list = $this->listModel->where('publicId', $listPublicId)->first();
        if (!$list) {
            throw new \RuntimeException('List not found', 404);
        }

        $now = date('Y-m-d H:i:s');

        $this->model->update($card->id, [
            'listId'    => $list->id,
            'index'     => $index,
            'updatedAt' => $now,
        ]);

        return $this->model->find($card->id);
    }

    public function toggleLabel(string $cardPublicId, string $labelPublicId): array
    {
        $card = $this->model->where('publicId', $cardPublicId)->first();
        if (!$card) {
            throw new \RuntimeException('Card not found', 404);
        }

        $label = $this->labelModel->where('publicId', $labelPublicId)->first();
        if (!$label) {
            throw new \RuntimeException('Label not found', 404);
        }

        $existing = $this->cardLabelModel->builder()
            ->where('cardId', $card->id)
            ->where('labelId', $label->id)
            ->get()
            ->getFirstRow();

        if ($existing) {
            $this->cardLabelModel->builder()
                ->where('cardId', $card->id)
                ->where('labelId', $label->id)
                ->delete();
            return ['newLabel' => false];
        } else {
            $this->cardLabelModel->builder()->insert([
                'cardId'  => $card->id,
                'labelId' => $label->id,
            ]);
            return ['newLabel' => true];
        }
    }

    public function toggleMember(string $cardPublicId, string $memberPublicId): array
    {
        $card = $this->model->where('publicId', $cardPublicId)->first();
        if (!$card) {
            throw new \RuntimeException('Card not found', 404);
        }

        $member = $this->workspaceMemberModel->where('publicId', $memberPublicId)->first();
        if (!$member) {
            throw new \RuntimeException('Member not found', 404);
        }

        $existing = $this->cardMemberModel->builder()
            ->where('cardId', $card->id)
            ->where('workspaceMemberId', $member->id)
            ->get()
            ->getFirstRow();

        if ($existing) {
            $this->cardMemberModel->builder()
                ->where('cardId', $card->id)
                ->where('workspaceMemberId', $member->id)
                ->delete();
            return ['newMember' => false];
        } else {
            $this->cardMemberModel->builder()->insert([
                'cardId'            => $card->id,
                'workspaceMemberId' => $member->id,
            ]);

            if ($member->userId && $member->userId !== $this->getCurrentUserId()) {
                $list = $this->listModel->find($card->listId);
                $workspaceId = $list ? $this->getWorkspaceIdByList($list->id) : null;
                $cardInfo = $list ? $this->getCardNavigationInfo($list->id) : [];
                $this->notificationService->create([
                    'type'        => 'card_assigned',
                    'title'       => 'You have been assigned to a card',
                    'data'        => [
                        'cardTitle'     => $card->title,
                        'cardPublicId'  => $card->publicId,
                        'workspaceSlug' => $cardInfo['workspaceSlug'] ?? null,
                        'boardSlug'     => $cardInfo['boardSlug'] ?? null,
                    ],
                    'userId'      => $member->userId,
                    'workspaceId' => $workspaceId,
                    'createdBy'   => $this->getCurrentUserId(),
                ]);
            }

            return ['newMember' => true];
        }
    }

    private function getWorkspaceIdByList(int $listId): ?string
    {
        $db = \Config\Database::connect();
        $row = $db->table('list')
            ->select('board.workspaceId')
            ->join('board', 'board.id = list.boardId')
            ->where('list.id', $listId)
            ->get()
            ->getFirstRow();
        return $row->workspaceId ?? null;
    }

    private function getCardNavigationInfo(int $listId): array
    {
        $db = \Config\Database::connect();
        $row = $db->table('list')
            ->select('board.slug as boardSlug, workspace.slug as workspaceSlug')
            ->join('board', 'board.id = list.boardId')
            ->join('workspace', 'workspace.id = board.workspaceId')
            ->where('list.id', $listId)
            ->get()
            ->getFirstRow();
        return $row ? (array) $row : [];
    }

    private function getCurrentUserId(): ?string
    {
        return service('request')->user?->id ?? null;
    }

    public function duplicate(string $publicId, string $userId): object
    {
        $original = $this->getById($publicId);
        $now = date('Y-m-d H:i:s');

        $maxIndex = $this->model->builder()
            ->selectMax('`index`')
            ->where('listId', $original->listId)
            ->where('deletedAt IS NULL')
            ->get()
            ->getRow()
            ->index ?? -1;

        $newId = $this->model->insert([
            'publicId'    => generatePublicId(),
            'title'       => $original->title . ' (copy)',
            'description' => $original->description,
            'index'       => (int)$maxIndex + 1,
            'listId'      => $original->listId,
            'dueDate'     => $original->dueDate ?? null,
            'createdBy'   => $userId,
            'createdAt'   => $now,
            'updatedAt'   => $now,
        ]);

        foreach ($original->labels as $label) {
            $this->cardLabelModel->builder()->insert(['cardId' => $newId, 'labelId' => $label->labelId]);
        }
        foreach ($original->members as $member) {
            $this->cardMemberModel->builder()->insert(['cardId' => $newId, 'workspaceMemberId' => $member->workspaceMemberId]);
        }

        return $this->model->find($newId);
    }

    public function delete(string $publicId): void
    {
        $card = $this->model->where('publicId', $publicId)->first();
        if (!$card) {
            throw new \RuntimeException('Card not found', 404);
        }
        $this->model->delete($card->id);
    }
}
