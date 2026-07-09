<?php

namespace Module\Comment\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table = 'card_comments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'comment', 'cardId',
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
