<?php

namespace Module\ApiKey\Models;

use CodeIgniter\Model;

class ApiKeyModel extends Model
{
    protected $table = 'api_keys';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'name', 'keyHash', 'keyPrefix', 'permissions', 'active',
        'workspaceId', 'createdBy', 'createdAt', 'lastUsedAt', 'revokedAt',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $returnType = 'object';
}
