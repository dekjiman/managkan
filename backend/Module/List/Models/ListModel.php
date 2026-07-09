<?php

namespace Module\List\Models;

use CodeIgniter\Model;

class ListModel extends Model
{
    protected $table = 'list';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'name', 'index', 'boardId',
        'createdBy', 'createdAt', 'updatedAt', 'deletedAt', 'deletedBy',
    ];
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'createdAt';
    protected $updatedField = 'updatedAt';
    protected $deletedField = 'deletedAt';
    protected $returnType = 'object';
}
