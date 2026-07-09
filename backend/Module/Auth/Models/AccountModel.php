<?php

namespace Module\Auth\Models;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $table = 'account';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'id', 'accountId', 'providerId', 'userId', 'accessToken', 'refreshToken', 'idToken',
        'accessTokenExpiresAt', 'refreshTokenExpiresAt', 'scope', 'password', 'createdAt', 'updatedAt',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'createdAt';
    protected $updatedField = 'updatedAt';
    protected $deletedField = 'deletedAt';
    protected $returnType = 'object';
}
