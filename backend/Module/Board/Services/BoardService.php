<?php

namespace Module\Board\Services;

use Module\Board\Models\BoardModel;
use Module\Board\Models\LabelModel;
use Module\List\Models\ListModel;
use Module\Card\Models\CardModel;
use Module\Card\Models\CardLabelModel;
use Module\Card\Models\CardMemberModel;
use Module\Workspace\Models\WorkspaceModel;
use Module\Workspace\Models\WorkspaceMemberModel;

class BoardService
{
    protected BoardModel $boardModel;
    protected LabelModel $labelModel;
    protected ListModel $listModel;
    protected CardModel $cardModel;
    protected CardLabelModel $cardLabelModel;
    protected CardMemberModel $cardMemberModel;
    protected WorkspaceModel $workspaceModel;
    protected WorkspaceMemberModel $workspaceMemberModel;

    public function __construct()
    {
        $this->boardModel = new BoardModel();
        $this->labelModel = new LabelModel();
        $this->listModel = new ListModel();
        $this->cardModel = new CardModel();
        $this->cardLabelModel = new CardLabelModel();
        $this->cardMemberModel = new CardMemberModel();
        $this->workspaceModel = new WorkspaceModel();
        $this->workspaceMemberModel = new WorkspaceMemberModel();
    }

    private function toSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    private function uniqueSlug(string $baseSlug, int $workspaceId): string
    {
        $slug = $baseSlug;
        $counter = 1;
        while (true) {
            $existing = $this->boardModel
                ->where('slug', $slug)
                ->where('workspaceId', $workspaceId)
                ->first();
            if (!$existing) return $slug;
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    public function getByWorkspace(string $wsPublicId, ?string $type = null): array
    {
        $workspace = $this->workspaceModel->where('publicId', $wsPublicId)->orWhere('slug', $wsPublicId)->first();
        if (!$workspace) {
            throw new \RuntimeException('Workspace not found', 404);
        }

        $builder = $this->boardModel->builder();
        $builder->where('workspaceId', $workspace->id);
        $builder->where('deletedAt IS NULL');

        if ($type !== null) {
            $builder->where('type', $type);
        } else {
            $builder->where('type !=', 'template');
        }

        $builder->orderBy('createdAt', 'DESC');

        return $builder->get()->getResultObject();
    }

    public function getById(string $publicId): object
    {
        $board = $this->boardModel->where('publicId', $publicId)->orWhere('slug', $publicId)->first();
        if (!$board) {
            throw new \RuntimeException('Board not found', 404);
        }

        $workspace = $this->workspaceModel->find($board->workspaceId);

        $lists = $this->listModel
            ->where('boardId', $board->id)
            ->where('deletedAt IS NULL')
            ->orderBy('index', 'ASC')
            ->findAll();

        $listIds = array_map(fn($l) => $l->id, $lists);
        $allCards = [];
        if (!empty($listIds)) {
            $allCards = $this->cardModel
                ->whereIn('listId', $listIds)
                ->where('deletedAt IS NULL')
                ->orderBy('index', 'ASC')
                ->findAll();
        }

        $cardIds = array_map(fn($c) => $c->id, $allCards);
        $cardLabelMap = [];
        $cardMemberMap = [];
        $labelMap = [];
        $memberMap = [];

        if (!empty($cardIds)) {
            $clRaw = $this->cardLabelModel->builder()
                ->whereIn('cardId', $cardIds)
                ->get()->getResultObject();
            $labelIds = array_unique(array_map(fn($cl) => $cl->labelId, $clRaw));
            if (!empty($labelIds)) {
                $labelRows = $this->labelModel->builder()->whereIn('id', $labelIds)->get()->getResultObject();
                foreach ($labelRows as $lb) {
                    $labelMap[$lb->id] = $lb;
                }
            }
            foreach ($clRaw as $cl) {
                $label = $labelMap[$cl->labelId] ?? null;
                if ($label) {
                    $cardLabelMap[$cl->cardId][] = (object)[
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
            $wmIds = array_unique(array_map(fn($cm) => $cm->workspaceMemberId, $cmRaw));
            if (!empty($wmIds)) {
                $wmRows = $this->workspaceMemberModel->builder()->whereIn('id', $wmIds)->get()->getResultObject();
                foreach ($wmRows as $wm) {
                    $memberMap[$wm->id] = $wm;
                }
            }
            foreach ($cmRaw as $cm) {
                $wm = $memberMap[$cm->workspaceMemberId] ?? null;
                if ($wm) {
                    $userName = null;
                    $userImage = null;
                    if ($wm->userId) {
                        $user = $this->getUser($wm->userId);
                        $userName = $user->name ?? null;
                        $userImage = $user->image ?? null;
                    }
                    $cardMemberMap[$cm->cardId][] = (object)[
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

        $labels = $this->labelModel
            ->where('boardId', $board->id)
            ->where('deletedAt IS NULL')
            ->findAll();

        $board->lists = [];
        foreach ($lists as $list) {
            $listCards = array_values(array_filter($allCards, fn($c) => $c->listId === $list->id));
            foreach ($listCards as $card) {
                $card->labels = $cardLabelMap[$card->id] ?? [];
                $card->members = $cardMemberMap[$card->id] ?? [];
            }
            $list->cards = $listCards;
            $board->lists[] = $list;
        }
        $board->labels = $labels;

        // Add workspace info with members
        $wsMembers = [];
        if ($workspace) {
            $memberRows = $this->workspaceMemberModel
                ->where('workspaceId', $workspace->id)
                ->where('deletedAt IS NULL')
                ->findAll();
            foreach ($memberRows as $wm) {
                $user = $wm->userId ? $this->getUser($wm->userId) : null;
                $wsMembers[] = (object)[
                    'id'        => (int)$wm->id,
                    'publicId'  => $wm->publicId,
                    'email'     => $wm->email,
                    'role'      => $wm->role,
                    'userId'    => $wm->userId,
                    'user'      => $user ? (object)[
                        'id'    => $user->id,
                        'name'  => $user->name ?? null,
                        'email' => $user->email,
                        'image' => $user->image ?? null,
                    ] : null,
                ];
            }
        }
        $board->workspace = (object)[
            'publicId' => $workspace->publicId ?? null,
            'members'  => $wsMembers,
        ];

        return $board;
    }

    private function getUser(string $userId): ?object
    {
        $db = \Config\Database::connect();
        $row = $db->table('user')->where('id', $userId)->get()->getFirstRow();
        return $row ?: null;
    }

    public function create(string $wsPublicId, array $data, string $userId): object
    {
        $workspace = $this->workspaceModel->where('publicId', $wsPublicId)->orWhere('slug', $wsPublicId)->first();
        if (!$workspace) {
            throw new \RuntimeException('Workspace not found', 404);
        }

        $baseSlug = $this->toSlug($data['name'] ?? 'untitled-board');
        $slug = $this->uniqueSlug($baseSlug, $workspace->id);
        $publicId = generatePublicId();
        $now = date('Y-m-d H:i:s');

        $boardId = $this->boardModel->insert([
            'publicId'    => $publicId,
            'name'        => $data['name'] ?? 'Untitled Board',
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'visibility'  => $data['visibility'] ?? 'private',
            'type'        => $data['type'] ?? 'regular',
            'workspaceId' => $workspace->id,
            'createdBy'   => $userId,
            'createdAt'   => $now,
            'updatedAt'   => $now,
        ]);

        $sourceBoardPublicId = $data['sourceBoardPublicId'] ?? null;
        if ($sourceBoardPublicId) {
            $source = $this->boardModel
                ->where('publicId', $sourceBoardPublicId)
                ->where('type', 'template')
                ->first();

            if ($source) {
                $sourceLists = $this->listModel
                    ->where('boardId', $source->id)
                    ->where('deletedAt IS NULL')
                    ->orderBy('index', 'ASC')
                    ->findAll();

                $sourceLabels = $this->labelModel
                    ->where('boardId', $source->id)
                    ->where('deletedAt IS NULL')
                    ->findAll();

                $labelIdMap = [];
                foreach ($sourceLabels as $sl) {
                    $newLabelId = $this->labelModel->insert([
                        'publicId'   => generatePublicId(),
                        'name'       => $sl->name,
                        'colourCode' => $sl->colourCode,
                        'boardId'    => $boardId,
                        'createdBy'  => $userId,
                        'createdAt'  => $now,
                        'updatedAt'  => $now,
                    ]);
                    $labelIdMap[$sl->id] = $newLabelId;
                }

                foreach ($sourceLists as $sl) {
                    $newListId = $this->listModel->insert([
                        'publicId'  => generatePublicId(),
                        'name'      => $sl->name,
                        'index'     => $sl->index,
                        'boardId'   => $boardId,
                        'createdBy' => $userId,
                        'createdAt' => $now,
                        'updatedAt' => $now,
                    ]);

                    $sourceCards = $this->cardModel
                        ->where('listId', $sl->id)
                        ->where('deletedAt IS NULL')
                        ->orderBy('index', 'ASC')
                        ->findAll();

                    foreach ($sourceCards as $sc) {
                        $newCardId = $this->cardModel->insert([
                            'publicId'    => generatePublicId(),
                            'title'       => $sc->title,
                            'description' => $sc->description,
                            'index'       => $sc->index,
                            'listId'      => $newListId,
                            'dueDate'     => $sc->dueDate ?? null,
                            'createdBy'   => $userId,
                            'createdAt'   => $now,
                            'updatedAt'   => $now,
                        ]);

                        $clSource = $this->cardLabelModel->builder()
                            ->where('cardId', $sc->id)
                            ->get()->getResultObject();
                        foreach ($clSource as $cl) {
                            $mappedId = $labelIdMap[$cl->labelId] ?? null;
                            if ($mappedId) {
                                $this->cardLabelModel->insert(['cardId' => $newCardId, 'labelId' => $mappedId]);
                            }
                        }
                    }
                }
            }
        } else {
            $listNames = $data['lists'] ?? ['To Do', 'In Progress', 'Done'];
            foreach ($listNames as $i => $name) {
                $this->listModel->insert([
                    'publicId'  => generatePublicId(),
                    'name'      => $name,
                    'index'     => $i,
                    'boardId'   => $boardId,
                    'createdBy' => $userId,
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ]);
            }

            $labelColors = ['#0d9488', '#65a30d', '#0284c7', '#4f46e5', '#ca8a04', '#ea580c', '#dc2626', '#db2777'];
            foreach ($data['labels'] ?? [] as $i => $labelName) {
                $this->labelModel->insert([
                    'publicId'   => generatePublicId(),
                    'name'       => $labelName,
                    'colourCode' => $labelColors[$i % count($labelColors)],
                    'boardId'    => $boardId,
                    'createdBy'  => $userId,
                    'createdAt'  => $now,
                    'updatedAt'  => $now,
                ]);
            }
        }

        return $this->boardModel->find($boardId);
    }

    public function update(string $publicId, array $data): object
    {
        $board = $this->boardModel->where('publicId', $publicId)->first();
        if (!$board) {
            throw new \RuntimeException('Board not found', 404);
        }

        $allowed = ['name', 'description', 'visibility'];
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
        $this->boardModel->update($board->id, $updateData);

        return $this->boardModel->find($board->id);
    }

    public function delete(string $publicId): void
    {
        $board = $this->boardModel->where('publicId', $publicId)->first();
        if (!$board) {
            throw new \RuntimeException('Board not found', 404);
        }
        $this->boardModel->delete($board->id);
    }

    public function checkSlugAvailability(string $slug): array
    {
        $existing = $this->boardModel->where('slug', $slug)->first();
        return ['isAvailable' => !$existing];
    }
}
