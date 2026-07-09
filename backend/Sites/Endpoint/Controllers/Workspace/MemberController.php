<?php

namespace Sites\Endpoint\Controllers\Workspace;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\Workspace\Services\MemberService;
use Module\Workspace\Models\WorkspaceModel;

class MemberController extends BaseApiController
{
    protected MemberService $memberService;
    protected WorkspaceModel $workspaceModel;

    public function __construct()
    {
        $this->memberService = new MemberService();
        $this->workspaceModel = new WorkspaceModel();
    }

    private function getWorkspaceSlug(): ?string
    {
        $qp = $this->request->getGet('workspaceId') ?: $this->request->getGet('workspaceSlug');
        if ($qp) return $qp;
        $uri = $this->request->getUri();
        if ($uri->getSegment(2) === 'workspaces') {
            return $uri->getSegment(3);
        }
        if ($uri->getSegment(1) === 'workspaces') {
            return $uri->getSegment(2);
        }
        return null;
    }

    private function resolveWorkspace(string $slug): object
    {
        $ws = $this->workspaceModel
            ->where('publicId', $slug)
            ->where('deletedAt IS NULL')
            ->first();
        if (!$ws) {
            $ws = $this->workspaceModel
                ->where('slug', $slug)
                ->where('deletedAt IS NULL')
                ->first();
        }
        if (!$ws && is_numeric($slug)) {
            $ws = $this->workspaceModel->find((int)$slug);
        }
        if (!$ws) {
            throw new \RuntimeException('Workspace not found', 404);
        }
        return $ws;
    }

    public function index(?string $wsPublicId = null)
    {
        try {
            $slug = $wsPublicId ?: $this->getWorkspaceSlug();
            if (!$slug) return $this->fail('Workspace identifier required', 400);
            $ws = $this->resolveWorkspace($slug);
            $members = $this->memberService->getByWorkspace((string) $ws->id);
            return $this->respond($members, 200, 'Members retrieved successfully');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function store(?string $wsPublicId = null)
    {
        try {
            $input = $this->getJsonInput();
            $slug = $wsPublicId ?: $this->getWorkspaceSlug() ?: $input['workspaceId'] ?? $input['workspaceSlug'] ?? null;
            if (!$slug) return $this->fail('Workspace identifier required', 400);
            $userId = $this->getUserId();
            if (!$userId) return $this->fail('Unauthorized', 401);
            $member = $this->memberService->add((string)$slug, $input, $userId);
            return $this->respond($member, 201, 'Member added successfully');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    private function getMemberPublicId(): ?string
    {
        $seg4 = $this->request->getUri()->getSegment(4);
        $seg3 = $this->request->getUri()->getSegment(3);
        $seg2 = $this->request->getUri()->getSegment(2);
        if ($seg4 && $seg4 !== 'members' && $seg4 !== 'resend-invite') return $seg4;
        if ($seg3 && $seg3 !== 'members' && $seg3 !== 'resend-invite') return $seg3;
        if ($seg2 && $seg2 !== 'members' && $seg2 !== 'resend-invite' && $seg2 !== 'workspaces') return $seg2;
        return null;
    }

    public function updateRole(?string $wsPublicId = null, ?string $memberPublicId = null)
    {
        try {
            $publicId = $memberPublicId ?: $this->getMemberPublicId();
            if (!$publicId) return $this->fail('Member identifier required', 400);
            $member = $this->memberService->getByPublicId($publicId);
            $slug = $this->getWorkspaceSlug() ?: (string) $member->workspaceId;
            $ws = $this->resolveWorkspace($slug);
            $input = $this->getJsonInput();
            $userId = $this->getUserId();
            $role = $input['role'] ?? '';
            $member = $this->memberService->updateRole($publicId, (string) $ws->id, $role, $userId);
            return $this->respond($member, 200, 'Member role updated successfully');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function destroy(?string $wsPublicId = null, ?string $memberPublicId = null)
    {
        try {
            $publicId = $memberPublicId ?: $this->getMemberPublicId();
            if (!$publicId) return $this->fail('Member identifier required', 400);
            $member = $this->memberService->getByPublicId($publicId);
            $slug = $this->getWorkspaceSlug() ?: (string) $member->workspaceId;
            $ws = $this->resolveWorkspace($slug);
            $userId = $this->getUserId();
            $this->memberService->remove($publicId, (string) $ws->id, $userId);
            return $this->respondMessage('Member removed successfully');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function inviteInfo()
    {
        try {
            $code = $this->request->getUri()->getSegment(4) ?: $this->request->getUri()->getSegment(3);
            $invite = $this->memberService->getByInviteCode($code);
            return $this->respond($invite, 200, 'Invite info retrieved successfully');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function acceptInvite()
    {
        try {
            $input = $this->getJsonInput();
            $code = $input['code'] ?? '';
            $name = $input['name'] ?? '';
            $password = $input['password'] ?? '';

            if (empty($code) || empty($name) || empty($password)) {
                return $this->fail('Code, name, and password are required', 400);
            }

            $result = $this->memberService->activateByInviteCode($code, $name, $password);
            return $this->response->setStatusCode(200)->setJSON($result);
        } catch (\RuntimeException $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }

    public function resendInvite(?string $wsPublicId = null, ?string $memberPublicId = null)
    {
        try {
            $publicId = $memberPublicId ?: $this->getMemberPublicId();
            if (!$publicId) return $this->fail('Member identifier required', 400);
            $member = $this->memberService->getByPublicId($publicId);
            $slug = $this->getWorkspaceSlug() ?: (string) $member->workspaceId;
            $ws = $this->resolveWorkspace($slug);
            $userId = $this->getUserId();
            $member = $this->memberService->regenerateInviteCode($publicId, (string) $ws->id, $userId);
            return $this->respond($member, 200, 'Invite resent successfully');
        } catch (\Throwable $e) {
            $code = $e->getCode();
            return $this->fail($e->getMessage(), is_int($code) && $code >= 400 && $code < 600 ? $code : 400);
        }
    }
}
