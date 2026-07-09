<?php

namespace Module\Workspace\Services;

use Module\Workspace\Models\WorkspaceMemberModel;
use Module\Workspace\Models\WorkspaceModel;
use Module\Auth\Models\UserModel;
use Module\Auth\Models\AccountModel;
use Module\Notification\Services\NotificationService;
use Module\Email\Services\EmailService;

class MemberService
{
    protected WorkspaceMemberModel $model;
    protected WorkspaceModel $workspaceModel;
    protected UserModel $userModel;
    protected AccountModel $accountModel;
    protected NotificationService $notificationService;
    protected EmailService $emailService;

    public function __construct()
    {
        $this->model = new WorkspaceMemberModel();
        $this->workspaceModel = new WorkspaceModel();
        $this->userModel = new UserModel();
        $this->accountModel = new AccountModel();
        $this->notificationService = new NotificationService();
        $this->emailService = new EmailService();
    }

    public function getByWorkspace(string $workspaceId): array
    {
        $builder = $this->model->builder();
        $builder->select("workspace_members.id, workspace_members.publicId, workspace_members.userId, workspace_members.workspaceId, workspace_members.createdBy, workspace_members.createdAt, workspace_members.updatedAt, workspace_members.deletedAt, workspace_members.deletedBy, workspace_members.role, workspace_members.status, workspace_members.inviteCode, COALESCE(user.email, workspace_members.email) as email, user.name, user.image")
            ->join('user', 'user.id = workspace_members.userId', 'left')
            ->where('workspace_members.workspaceId', $workspaceId)
            ->where('workspace_members.deletedAt IS NULL');
        return $builder->get()->getResultObject();
    }

    public function getByPublicId(string $publicId): object
    {
        $member = $this->model
            ->where('publicId', $publicId)
            ->where('deletedAt IS NULL')
            ->first();

        if (!$member) {
            throw new \RuntimeException('Member not found', 404);
        }

        return $member;
    }

    public function getById(string $publicId, string $workspaceId): object
    {
        $member = $this->model
            ->where('publicId', $publicId)
            ->where('workspaceId', $workspaceId)
            ->where('deletedAt IS NULL')
            ->first();

        if (!$member) {
            throw new \RuntimeException('Member not found', 404);
        }

        return $member;
    }

    public function add(string $workspaceId, array $data, string $userId): object
    {
        $workspace = $this->workspaceModel
            ->where('publicId', $workspaceId)
            ->where('deletedAt IS NULL')
            ->first();
        if (!$workspace) {
            $workspace = $this->workspaceModel
                ->where('slug', $workspaceId)
                ->where('deletedAt IS NULL')
                ->first();
        }
        if (!$workspace && is_numeric($workspaceId)) {
            $workspace = $this->workspaceModel->find((int)$workspaceId);
        }
        if (!$workspace) {
            throw new \RuntimeException('Workspace not found', 404);
        }

        $currentMember = $this->model
            ->where('workspaceId', $workspace->id)
            ->where('userId', $userId)
            ->where('deletedAt IS NULL')
            ->first();

        if (!$currentMember || $currentMember->role !== 'admin') {
            throw new \RuntimeException('Only admins can add members', 403);
        }

        $memberId = null;
        $memberEmail = $data['email'] ?? null;
        $targetUserId = $data['userId'] ?? null;

        if ($targetUserId) {
            $targetUser = $this->userModel->find($targetUserId);
            if (!$targetUser) {
                throw new \RuntimeException('User not found', 404);
            }
            $memberEmail = $targetUser->email;

            $existing = $this->model
                ->where('workspaceId', $workspace->id)
                ->where('userId', $targetUserId)
                ->where('deletedAt IS NULL')
                ->first();

            if ($existing) {
                throw new \RuntimeException('User is already a member of this workspace', 409);
            }

            $memberId = $this->model->insert([
                'publicId'    => generatePublicId(),
                'userId'      => $targetUserId,
                'workspaceId' => $workspace->id,
                'createdBy'   => $userId,
                'role'        => $data['role'] ?? 'member',
                'email'       => $memberEmail,
                'status'      => 'active',
                'inviteCode'  => bin2hex(random_bytes(16)),
            ]);
        } elseif ($memberEmail) {
            $targetUser = $this->userModel->where('email', $memberEmail)->first();

            if ($targetUser) {
                $targetUserId = $targetUser->id;
                $existing = $this->model
                    ->where('workspaceId', $workspace->id)
                    ->where('userId', $targetUserId)
                    ->where('deletedAt IS NULL')
                    ->first();

                if ($existing) {
                    throw new \RuntimeException('User is already a member of this workspace', 409);
                }

                $inviteCode = bin2hex(random_bytes(16));
                $memberId = $this->model->insert([
                    'publicId'    => generatePublicId(),
                    'userId'      => $targetUserId,
                    'workspaceId' => $workspace->id,
                    'createdBy'   => $userId,
                    'role'        => $data['role'] ?? 'member',
                    'email'       => $memberEmail,
                    'status'      => 'pending',
                    'inviteCode'  => $inviteCode,
                ]);

                $inviter = $this->userModel->find($userId);
                $inviterName = $inviter->name ?? 'Someone';
                try {
                    $this->emailService->sendInvite($memberEmail, $inviteCode, $workspace->name, $inviterName);
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to send invite email: ' . $e->getMessage());
                }
            } else {
                $existing = $this->model
                    ->where('workspaceId', $workspace->id)
                    ->where('email', $memberEmail)
                    ->where('deletedAt IS NULL')
                    ->first();

                if ($existing) {
                    throw new \RuntimeException('A pending invitation already exists for this email', 409);
                }

                $inviteCode = bin2hex(random_bytes(16));
                $memberId = $this->model->insert([
                    'publicId'    => generatePublicId(),
                    'userId'      => null,
                    'workspaceId' => $workspace->id,
                    'createdBy'   => $userId,
                    'role'        => $data['role'] ?? 'member',
                    'email'       => $memberEmail,
                    'status'      => 'pending',
                    'inviteCode'  => $inviteCode,
                ]);

                $inviter = $this->userModel->find($userId);
                $inviterName = $inviter->name ?? 'Someone';
                try {
                    $this->emailService->sendInvite($memberEmail, $inviteCode, $workspace->name, $inviterName);
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to send invite email: ' . $e->getMessage());
                }
            }
        } else {
            throw new \RuntimeException('Either userId or email is required', 400);
        }

        if ($targetUserId) {
            $this->notificationService->create([
                'type'        => 'member_added',
                'data'        => ['workspaceId' => $workspace->publicId, 'workspaceName' => $workspace->name],
                'userId'      => $targetUserId,
                'workspaceId' => $workspace->id,
                'createdBy'   => $userId,
            ]);
        }

        return $this->model->find($memberId);
    }

    public function updateRole(string $publicId, string $workspaceId, string $role, string $userId): object
    {
        $member = $this->getById($publicId, $workspaceId);

        $currentMember = $this->model
            ->where('workspaceId', $workspaceId)
            ->where('userId', $userId)
            ->where('deletedAt IS NULL')
            ->first();

        if (!$currentMember || $currentMember->role !== 'admin') {
            throw new \RuntimeException('Only admins can update member roles', 403);
        }

        if (!in_array($role, ['admin', 'member', 'viewer'], true)) {
            throw new \RuntimeException('Invalid role', 400);
        }

        $this->model->update($member->id, ['role' => $role]);

        return $this->model->find($member->id);
    }

    public function remove(string $publicId, string $workspaceId, string $userId): void
    {
        $member = $this->getById($publicId, $workspaceId);

        $currentMember = $this->model
            ->where('workspaceId', $workspaceId)
            ->where('userId', $userId)
            ->where('deletedAt IS NULL')
            ->first();

        if (!$currentMember || $currentMember->role !== 'admin') {
            throw new \RuntimeException('Only admins can remove members', 403);
        }

        if ($member->userId === $userId) {
            throw new \RuntimeException('Cannot remove yourself from workspace', 400);
        }

        $this->model->delete($member->id);
    }

    public function activateByEmail(string $email, string $workspaceId): object
    {
        $member = $this->model
            ->where('email', $email)
            ->where('workspaceId', $workspaceId)
            ->where('status', 'pending')
            ->where('deletedAt IS NULL')
            ->first();

        if (!$member) {
            throw new \RuntimeException('No pending invitation found for this email', 404);
        }

        $user = $this->userModel->where('email', $email)->first();
        if (!$user) {
            throw new \RuntimeException('User not found for this email', 404);
        }

        $this->model->update($member->id, [
            'userId' => $user->id,
            'status' => 'active',
        ]);

        return $this->model->find($member->id);
    }

    public function getByInviteCode(string $code): object
    {
        $builder = $this->model->builder();
        $builder->select('workspace_members.*, workspace.name as workspaceName, workspace.slug as workspaceSlug')
            ->join('workspace', 'workspace.id = workspace_members.workspaceId', 'left')
            ->where('workspace_members.inviteCode', $code)
            ->where('workspace_members.deletedAt IS NULL')
            ->where('workspace.deletedAt IS NULL');

        $member = $builder->get()->getRow();
        if (!$member) {
            throw new \RuntimeException('Invalid invite code', 404);
        }

        return $member;
    }

    public function activateByInviteCode(string $code, string $name, string $password): array
    {
        $member = $this->model
            ->where('inviteCode', $code)
            ->where('status', 'pending')
            ->where('deletedAt IS NULL')
            ->first();

        if (!$member) {
            throw new \RuntimeException('Invalid or already used invite code', 404);
        }

        $email = $member->email;
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');
        $userId = $this->generateUuid();

        $db = \Config\Database::connect();
        $db->transStart();

        $this->userModel->insert([
            'id'            => $userId,
            'name'          => $name,
            'email'         => $email,
            'emailVerified' => 0,
            'createdAt'     => $now,
            'updatedAt'     => $now,
        ]);

        $this->accountModel->insert([
            'id'         => $this->generateUuid(),
            'accountId'  => $email,
            'providerId' => 'credentials',
            'userId'     => $userId,
            'password'   => $hashedPassword,
            'createdAt'  => $now,
            'updatedAt'  => $now,
        ]);

        $this->model->update($member->id, [
            'userId' => $userId,
            'status' => 'active',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \RuntimeException('Failed to create account', 500);
        }

        $user = $this->userModel->find($userId);

        $jwtSecret = getenv('jwt.secret') ?: 'managpro-jwt-secret';
        $accessExpiry = (int)(getenv('jwt.accessExpiry') ?: 3600);
        $refreshExpiry = (int)(getenv('jwt.refreshExpiry') ?: 1209600);

        $nowTs = time();
        $accessToken = \Firebase\JWT\JWT::encode([
            'sub'   => $user->id,
            'email' => $user->email,
            'name'  => $user->name,
            'image' => $user->image ?? null,
            'iat'   => $nowTs,
            'exp'   => $nowTs + $accessExpiry,
        ], $jwtSecret, 'HS256');

        $refreshToken = \Firebase\JWT\JWT::encode([
            'sub'  => $user->id,
            'type' => 'refresh',
            'iat'  => $nowTs,
            'exp'  => $nowTs + $refreshExpiry,
        ], $jwtSecret, 'HS256');

        $sessionModel = new \Module\Auth\Models\SessionModel();
        $sessionModel->insert([
            'id'        => $this->generateUuid(),
            'userId'    => $user->id,
            'token'     => $refreshToken,
            'expiresAt' => date('Y-m-d H:i:s', $nowTs + $refreshExpiry),
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);

        return [
            'user'         => $user,
            'accessToken'  => $accessToken,
            'refreshToken' => $refreshToken,
        ];
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    public function getByIdWithWorkspace(string $publicId): object
    {
        $builder = $this->model->builder();
        $builder->select('workspace_members.*, workspace.name as workspaceName, workspace.slug as workspaceSlug, workspace.publicId as workspacePublicId')
            ->join('workspace', 'workspace.id = workspace_members.workspaceId', 'left')
            ->where('workspace_members.publicId', $publicId)
            ->where('workspace_members.deletedAt IS NULL')
            ->where('workspace.deletedAt IS NULL');

        $member = $builder->get()->getRow();
        if (!$member) {
            throw new \RuntimeException('Member not found', 404);
        }

        return $member;
    }

    public function regenerateInviteCode(string $publicId, string $workspaceId, string $userId): object
    {
        $member = $this->getById($publicId, $workspaceId);

        $currentMember = $this->model
            ->where('workspaceId', $workspaceId)
            ->where('userId', $userId)
            ->where('deletedAt IS NULL')
            ->first();

        if (!$currentMember || $currentMember->role !== 'admin') {
            throw new \RuntimeException('Only admins can regenerate invite codes', 403);
        }

        $newInviteCode = bin2hex(random_bytes(16));
        $this->model->update($member->id, [
            'inviteCode' => $newInviteCode,
        ]);

        $inviter = $this->userModel->find($userId);
        $inviterName = $inviter->name ?? 'Someone';
        $builder = $this->workspaceModel->builder();
        $workspace = $builder->where('id', $workspaceId)->where('deletedAt IS NULL')->get()->getRow();
        if ($workspace && $member->email) {
            try {
                $this->emailService->sendInvite($member->email, $newInviteCode, $workspace->name, $inviterName);
            } catch (\Throwable $e) {
                log_message('error', 'Failed to send invite email: ' . $e->getMessage());
            }
        }

        return $this->model->find($member->id);
    }
}
