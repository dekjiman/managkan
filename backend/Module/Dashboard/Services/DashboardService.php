<?php

namespace Module\Dashboard\Services;

use Module\Board\Models\BoardModel;
use Module\List\Models\ListModel;
use Module\Card\Models\CardModel;
use Module\Activity\Models\ActivityModel;
use Module\Workspace\Models\WorkspaceModel;
use Module\Workspace\Models\WorkspaceMemberModel;

class DashboardService
{
    protected BoardModel $boardModel;
    protected ListModel $listModel;
    protected CardModel $cardModel;
    protected ActivityModel $activityModel;
    protected WorkspaceModel $workspaceModel;
    protected WorkspaceMemberModel $memberModel;

    public function __construct()
    {
        $this->boardModel = new BoardModel();
        $this->listModel = new ListModel();
        $this->cardModel = new CardModel();
        $this->activityModel = new ActivityModel();
        $this->workspaceModel = new WorkspaceModel();
        $this->memberModel = new WorkspaceMemberModel();
    }

    public function getData(string $userId, ?string $workspacePublicId = null): array
    {
        $workspaceIds = [];

        if ($workspacePublicId) {
            $workspace = $this->workspaceModel->where('publicId', $workspacePublicId)->orWhere('slug', $workspacePublicId)->first();
            if (!$workspace) throw new \RuntimeException('Workspace not found', 404);
            $workspaceIds = [$workspace->id];
        } else {
            $memberships = $this->memberModel->where('userId', $userId)->where('deletedAt', null)->findAll();
            $workspaceIds = array_map(fn($m) => $m->workspaceId, $memberships);
        }

        if (empty($workspaceIds)) {
            return [
                'summary'           => ['workspaces' => 0, 'boards' => 0, 'cards' => 0, 'members' => 0],
                'cardsPerList'      => [],
                'cardsOverTime'     => [],
                'overdue'           => [],
                'dueSoon'           => [],
                'userActivity'      => [],
                'boardsPerWorkspace' => [],
            ];
        }

        $boards = $this->boardModel
            ->whereIn('workspaceId', $workspaceIds)
            ->where('deletedAt', null)
            ->findAll();
        $boardIds = array_map(fn($b) => $b->id, $boards);

        $boardWorkspaceMap = [];
        foreach ($boards as $b) {
            $boardWorkspaceMap[$b->id] = $b->workspaceId;
        }

        $lists = [];
        $cards = [];
        if (!empty($boardIds)) {
            $lists = $this->listModel
                ->whereIn('boardId', $boardIds)
                ->where('deletedAt', null)
                ->findAll();
            $listIds = array_map(fn($l) => $l->id, $lists);

            if (!empty($listIds)) {
                $cards = $this->cardModel
                    ->whereIn('listId', $listIds)
                    ->where('deletedAt', null)
                    ->findAll();
            }
        }

        $now = date('Y-m-d H:i:s');
        $twelveWeeksAgo = date('Y-m-d H:i:s', strtotime('-12 weeks'));

        $overdue = [];
        $dueSoon = [];
        if (!empty($cards)) {
            $overdue = array_filter($cards, fn($c) => $c->dueDate && $c->dueDate < $now);
            $dueSoon = array_filter($cards, fn($c) => $c->dueDate && $c->dueDate >= $now && $c->dueDate <= date('Y-m-d H:i:s', strtotime('+7 days')));
        }

        $cardsPerList = [];
        if (!empty($lists)) {
            foreach ($lists as $list) {
                $count = count(array_filter($cards, fn($c) => $c->listId === $list->id));
                $cardsPerList[] = ['listId' => $list->publicId, 'title' => $list->name, 'count' => $count];
            }
        }

        $cardsOverTime = [];
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = date('Y-m-d H:i:s', strtotime("-$i weeks"));
            $weekEnd = date('Y-m-d H:i:s', strtotime(-$i + 1 . ' weeks'));
            $count = !empty($cards) ? count(array_filter($cards, fn($c) => $c->createdAt >= $weekStart && $c->createdAt < $weekEnd)) : 0;
            $cardsOverTime[] = ['date' => date('Y-m-d', strtotime($weekStart)), 'count' => $count];
        }

        $activity = [];
        if (!empty($boardIds)) {
            $activity = $this->activityModel
                ->select('card_activity.*')
                ->join('card', 'card.id = card_activity.cardId')
                ->join('list', 'list.id = card.listId')
                ->whereIn('list.boardId', $boardIds)
                ->where('card_activity.createdAt >=', $twelveWeeksAgo)
                ->orderBy('card_activity.createdAt', 'DESC')
                ->findAll(50);
        }

        $userActivity = array_map(fn($a) => [
            'publicId'  => $a->publicId,
            'type'      => $a->type,
            'data'      => $a->data ?? null,
            'createdAt' => $a->createdAt,
        ], $activity);

        $boardsPerWorkspace = [];
        foreach ($boards as $board) {
            $wid = $board->workspaceId;
            if (!isset($boardsPerWorkspace[$wid])) {
                $w = $this->workspaceModel->find($wid);
                $boardsPerWorkspace[$wid] = ['workspacePublicId' => $w->publicId ?? '', 'name' => $w->name ?? '', 'count' => 0];
            }
            $boardsPerWorkspace[$wid]['count']++;
        }
        $boardsPerWorkspace = array_values($boardsPerWorkspace);

        $overdueCards = array_map(function($c) {
            $list = null;
            $board = null;
            $workspace = null;
            if ($c->listId) { $list = $this->listModel->find($c->listId); }
            if ($list) { $board = $this->boardModel->find($list->boardId); }
            if ($board) { $workspace = $this->workspaceModel->find($board->workspaceId); }
            return [
                'publicId'      => $c->publicId,
                'title'         => $c->title,
                'dueDate'       => $c->dueDate,
                'listName'      => $list->name ?? null,
                'boardName'     => $board->name ?? null,
                'boardSlug'     => $board->slug ?? null,
                'workspaceSlug' => $workspace->slug ?? null,
            ];
        }, $overdue);

        $dueSoonCards = array_map(function($c) {
            $list = null;
            $board = null;
            $workspace = null;
            if ($c->listId) { $list = $this->listModel->find($c->listId); }
            if ($list) { $board = $this->boardModel->find($list->boardId); }
            if ($board) { $workspace = $this->workspaceModel->find($board->workspaceId); }
            return [
                'publicId'      => $c->publicId,
                'title'         => $c->title,
                'dueDate'       => $c->dueDate,
                'listName'      => $list->name ?? null,
                'boardName'     => $board->name ?? null,
                'boardSlug'     => $board->slug ?? null,
                'workspaceSlug' => $workspace->slug ?? null,
            ];
        }, $dueSoon);

        return [
            'summary'           => [
                'workspaces' => count($workspaceIds),
                'boards'     => count($boards),
                'cards'      => count($cards),
                'members'    => $this->memberModel->whereIn('workspaceId', $workspaceIds)->where('deletedAt', null)->countAllResults(),
            ],
            'cardsPerList'      => array_map(fn($cpl) => ['name' => $cpl['title'], 'count' => $cpl['count']], $cardsPerList),
            'cardsOverTime'     => $cardsOverTime,
            'overdue'           => ['count' => count($overdue), 'cards' => array_values($overdueCards)],
            'dueSoon'           => ['count' => count($dueSoon), 'cards' => array_values($dueSoonCards)],
            'userActivity'      => $userActivity,
            'boardsPerWorkspace' => $boardsPerWorkspace,
        ];
    }
}
