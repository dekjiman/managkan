<?php

namespace Module\Billing\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table = 'subscriptions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'workspaceId', 'plan', 'status',
        'startDate', 'endDate', 'midtransOrderId', 'paymentAmount',
        'createdAt', 'updatedAt',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $returnType = 'object';
}
