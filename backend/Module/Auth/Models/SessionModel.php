<?php

namespace Module\Auth\Models;

use CodeIgniter\Model;

class SessionModel extends Model
{
    protected $table = 'session';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'id', 'expiresAt', 'token', 'createdAt', 'updatedAt', 'ipAddress', 'userAgent', 'userId',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'createdAt';
    protected $updatedField = 'updatedAt';
    protected $deletedField = 'deletedAt';
    protected $returnType = 'object';
}
