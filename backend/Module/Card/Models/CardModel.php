<?php

namespace Module\Card\Models;

use CodeIgniter\Model;

class CardModel extends Model
{
    protected $table = 'card';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'title', 'description', 'index', 'listId',
        'createdBy', 'createdAt', 'updatedAt', 'deletedAt', 'deletedBy',
        'dueDate', 'cardNumber',
    ];
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'createdAt';
    protected $updatedField = 'updatedAt';
    protected $deletedField = 'deletedAt';
    protected $returnType = 'object';
}
