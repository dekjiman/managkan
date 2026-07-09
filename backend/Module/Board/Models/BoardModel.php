<?php

namespace Module\Board\Models;

use CodeIgniter\Model;

class BoardModel extends Model
{
    protected $table = 'board';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'name', 'slug', 'description', 'workspaceId',
        'visibility', 'type', 'createdBy', 'createdAt', 'updatedAt', 'deletedAt', 'deletedBy',
    ];
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'createdAt';
    protected $updatedField = 'updatedAt';
    protected $deletedField = 'deletedAt';
    protected $returnType = 'object';
}
