<?php

namespace Module\Plan\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table = 'plans';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'name', 'displayName', 'price', 'currency',
        'boardLimit', 'memberLimit', 'workspaceLimit', 'storageLimit',
        'features', 'isActive', 'createdAt', 'updatedAt',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $returnType = 'object';
}
