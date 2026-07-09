<?php

namespace Module\Notification\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'userId', 'workspaceId', 'type', 'title',
        'entityType', 'entityId', 'entityUrl', 'data', 'read',
        'createdBy', 'createdAt',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $returnType = 'object';
}
