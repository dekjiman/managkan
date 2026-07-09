<?php

namespace Sites\Endpoint\Controllers\User;

use Sites\Endpoint\Controllers\BaseApiController;
use Module\User\Services\UserService;

class UserController extends BaseApiController
{
    protected UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function me()
    {
        try {
            $userId = $this->getUserId();
            $user = $this->userService->getById($userId);
            return $this->respond($user, 200, 'User retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function updateMe()
    {
        try {
            $userId = $this->getUserId();
            $input = $this->getJsonInput();
            $user = $this->userService->update($userId, $input);
            return $this->respond($user, 200, 'User updated successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function deleteMe()
    {
        try {
            $userId = $this->getUserId();
            $this->userService->deleteAccount($userId);
            return $this->respondMessage('Account deleted successfully');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
