<?php

namespace Module\Activity\Models;

use CodeIgniter\Model;

class ActivityModel extends Model
{
    protected $table = 'card_activity';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'type', 'cardId',
        'fromIndex', 'toIndex', 'fromListId', 'toListId',
        'labelId', 'workspaceMemberId',
        'fromTitle', 'toTitle', 'fromDescription', 'toDescription',
        'createdBy', 'createdAt',
        'commentId', 'fromComment', 'toComment',
        'sourceBoardId', 'fromDueDate', 'toDueDate', 'attachmentId',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $returnType = 'object';
}
