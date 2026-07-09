<?php

namespace Module\Checklist\Models;

use CodeIgniter\Model;

class CardChecklistItemModel extends Model
{
    protected $table = 'card_checklist_item';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'title', 'completed', 'index', 'checklistId',
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
