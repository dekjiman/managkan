<?php

namespace Module\Checklist\Models;

use CodeIgniter\Model;

class CardChecklistModel extends Model
{
    protected $table = 'card_checklist';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'name', 'index', 'cardId',
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
