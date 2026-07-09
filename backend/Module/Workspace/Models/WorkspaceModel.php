<?php

namespace Module\Workspace\Models;

use CodeIgniter\Model;

class WorkspaceModel extends Model
{
    protected $table = 'workspace';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'publicId',
        'name',
        'slug',
        'description',
        'plan',
        'createdBy',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'deletedBy',
    ];
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'createdAt';
    protected $updatedField = 'updatedAt';
    protected $deletedField = 'deletedAt';
    protected $returnType = 'object';
}
