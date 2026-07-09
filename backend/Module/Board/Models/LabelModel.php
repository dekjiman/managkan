<?php

namespace Module\Board\Models;

use CodeIgniter\Model;

class LabelModel extends Model
{
    protected $table = 'label';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'publicId',
        'name',
        'colourCode',
        'createdBy',
        'createdAt',
        'updatedAt',
        'boardId',
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
