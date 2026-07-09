<?php

namespace Module\Webhook\Models;

use CodeIgniter\Model;

class WebhookModel extends Model
{
    protected $table = 'webhooks';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'name', 'url', 'events', 'active',
        'workspaceId', 'createdBy', 'createdAt', 'lastDeliveryAt', 'lastDeliveryStatus',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $returnType = 'object';
}
