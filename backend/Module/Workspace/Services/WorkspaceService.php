<?php

namespace Module\Workspace\Services;

use Module\Workspace\Models\WorkspaceModel;
use Module\Workspace\Models\WorkspaceMemberModel;
use Module\Plan\Models\PlanModel;

class WorkspaceService
{
    protected WorkspaceModel $workspaceModel;
    protected WorkspaceMemberModel $memberModel;
    protected PlanModel $planModel;

    public function __construct()
    {
        $this->workspaceModel = new WorkspaceModel();
        $this->memberModel = new WorkspaceMemberModel();
        $this->planModel = new PlanModel();
    }

    private const RESERVED_SLUGS = ['dashboard', 'login', 'register', 'settings', 'api', 'admin', 'app', 'new', 'edit', 'delete'];

    private function toSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 1;
        while (true) {
            $existing = $this->workspaceModel->where('slug', $slug)->first();
            if (!$existing) return $slug;
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
    }

    public function getByUser(string $userId): array
    {
        $builder = $this->workspaceModel->builder();
        $builder->select('workspace.*, workspace_members.role')
            ->join('workspace_members', 'workspace.id = workspace_members.workspaceId')
            ->where('workspace_members.userId', $userId)
            ->where('workspace.deletedAt IS NULL');
        return $builder->get()->getResultObject();
    }

    public function getById(string $publicIdOrSlug, string $userId): object
    {
        $ws = $this->workspaceModel
            ->groupStart()
            ->where('publicId', $publicIdOrSlug)
            ->orWhere('slug', $publicIdOrSlug)
            ->groupEnd()
            ->where('deletedAt IS NULL')
            ->first();

        if (!$ws) {
            throw new \RuntimeException('Workspace not found', 404);
        }

        $member = $this->memberModel
            ->where('workspaceId', $ws->id)
            ->where('userId', $userId)
            ->first();

        if (!$member) {
            throw new \RuntimeException('No access to workspace', 403);
        }

        $ws->role = $member->role;
        return $ws;
    }

    public function create(array $data, string $userId): object
    {
        $freePlan = $this->planModel->where('name', 'free')->first();
        $workspaceLimit = $freePlan->workspaceLimit ?? 3;

        $builder = $this->memberModel->builder();
        $builder->select('COUNT(*) as count')
            ->join('workspace', 'workspace_members.workspaceId = workspace.id')
            ->where('workspace_members.userId', $userId)
            ->where('workspace.deletedAt IS NULL');
        $countResult = $builder->get()->getRow();

        if ($countResult && (int)$countResult->count >= $workspaceLimit) {
            throw new \RuntimeException("Workspace limit reached ({$workspaceLimit}). Upgrade your plan.", 403);
        }

        $baseSlug = $this->toSlug($data['name']);
        if (!$baseSlug) {
            throw new \RuntimeException('Invalid workspace name', 400);
        }
        $slug = $this->uniqueSlug($baseSlug);
        $publicId = generatePublicId();

        $db = \Config\Database::connect();
        $db->transStart();

        $inserted = $this->workspaceModel->insert([
            'publicId'  => $publicId,
            'name'      => $data['name'],
            'slug'      => $slug,
            'description' => $data['description'] ?? null,
            'createdBy' => $userId,
        ]);

        if ($inserted === false) {
            $db->transComplete();
            throw new \RuntimeException('Failed to create workspace', 500);
        }

        $wsId = $this->workspaceModel->getInsertID();

        $this->memberModel->insert([
            'publicId'    => generatePublicId(),
            'userId'      => $userId,
            'workspaceId' => $wsId,
            'createdBy'   => $userId,
            'role'        => 'admin',
            'email'       => '',
            'status'      => 'active',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \RuntimeException('Failed to create workspace', 500);
        }

        return $this->workspaceModel->find($wsId);
    }

    public function update(string $publicId, array $data, string $userId): object
    {
        $ws = $this->getById($publicId, $userId);
        if ($ws->role !== 'admin') {
            throw new \RuntimeException('Only admins can update workspace', 403);
        }

        $updateData = [];
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];

        $this->workspaceModel->update($ws->id, $updateData);
        return $this->workspaceModel->find($ws->id);
    }

    public function delete(string $publicId, string $userId): void
    {
        $ws = $this->getById($publicId, $userId);
        if ($ws->role !== 'admin') {
            throw new \RuntimeException('Only admins can delete workspace', 403);
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $now = date('Y-m-d H:i:s');

        $boardIds = $db->table('board')
            ->select('id')
            ->where('workspaceId', $ws->id)
            ->where('deletedAt IS NULL')
            ->get()->getResult();

        if (!empty($boardIds)) {
            $boardIdArr = array_column($boardIds, 'id');

            $listIds = $db->table('list')
                ->select('id')
                ->whereIn('boardId', $boardIdArr)
                ->where('deletedAt IS NULL')
                ->get()->getResult();

            $listIdArr = array_column($listIds, 'id');

            if (!empty($listIdArr)) {
                $cardIds = $db->table('card')
                    ->select('id')
                    ->whereIn('listId', $listIdArr)
                    ->where('deletedAt IS NULL')
                    ->get()->getResult();

                $cardIdArr = array_column($cardIds, 'id');

                if (!empty($cardIdArr)) {
                    $db->table('card')->whereIn('id', $cardIdArr)->update(['deletedAt' => $now, 'deletedBy' => $userId]);
                    $db->table('_card_labels')->whereIn('cardId', $cardIdArr)->delete();
                    $db->table('_card_workspace_members')->whereIn('cardId', $cardIdArr)->delete();
                    $db->table('card_checklist')->whereIn('cardId', $cardIdArr)->update(['deletedAt' => $now, 'deletedBy' => $userId]);
                    $db->table('card_comments')->whereIn('cardId', $cardIdArr)->update(['deletedAt' => $now, 'deletedBy' => $userId]);
                    $db->table('card_attachment')->whereIn('cardId', $cardIdArr)->update(['deletedAt' => $now]);
                }

                $db->table('list')->whereIn('id', $listIdArr)->update(['deletedAt' => $now, 'deletedBy' => $userId]);
            }

            $db->table('board')->whereIn('id', $boardIdArr)->update(['deletedAt' => $now, 'deletedBy' => $userId]);
            $db->table('label')->whereIn('boardId', $boardIdArr)->update(['deletedAt' => $now, 'deletedBy' => $userId]);
        }

        $db->table('workspace_members')->where('workspaceId', $ws->id)->delete();
        $db->table('workspace')->where('id', $ws->id)->update(['deletedAt' => $now, 'deletedBy' => $userId]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \RuntimeException('Failed to delete workspace', 500);
        }
    }

    public function checkSlugAvailability(string $slug): array
    {
        $isReserved = in_array($slug, self::RESERVED_SLUGS);
        if ($isReserved) return ['isAvailable' => false, 'isReserved' => true];

        $existing = $this->workspaceModel->where('slug', $slug)->first();
        return ['isAvailable' => !$existing, 'isReserved' => false];
    }
}
