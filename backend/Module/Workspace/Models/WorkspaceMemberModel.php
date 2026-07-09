<?php

namespace Module\Workspace\Models;

use CodeIgniter\Model;

class WorkspaceMemberModel extends Model
{
    protected $table = 'workspace_members';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'publicId',
        'userId',
        'workspaceId',
        'createdBy',
        'createdAt',
        'updatedAt',
        'deletedAt',
        'deletedBy',
        'role',
        'status',
        'email',
        'roleId',
        'inviteCode',
    ];
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'createdAt';
    protected $updatedField = 'updatedAt';
    protected $deletedField = 'deletedAt';
    protected $returnType = 'object';
}
