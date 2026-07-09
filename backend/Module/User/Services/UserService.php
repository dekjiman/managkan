<?php

namespace Module\User\Services;

use Module\Auth\Models\UserModel;
use Module\Auth\Models\AccountModel;
use Module\Auth\Models\SessionModel;

class UserService
{
    protected UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function getById(string $id): object
    {
        $user = $this->model->find($id);
        if (!$user) {
            throw new \RuntimeException('User not found', 404);
        }
        return $user;
    }

    public function getByEmail(string $email): ?object
    {
        return $this->model->where('email', $email)->first();
    }

    public function update(string $id, array $data): object
    {
        $user = $this->model->find($id);
        if (!$user) {
            throw new \RuntimeException('User not found', 404);
        }

        $allowed = ['name', 'email', 'image'];
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
        $this->model->update($id, $updateData);

        return $this->model->find($id);
    }

    public function deleteAccount(string $userId): void
    {
        $db = \Config\Database::connect();

        $user = $this->model->find($userId);
        if (!$user) throw new \RuntimeException('User not found', 404);

        $db->table('session')->where('userId', $userId)->delete();
        $db->table('account')->where('userId', $userId)->delete();
        $db->table('workspace_members')->where('userId', $userId)->delete();
        $db->table('notifications')->where('userId', $userId)->delete();
        $db->table('verification')->where('identifier', $user->email)->delete();
        $db->table('user')->where('id', $userId)->delete();
    }
}
