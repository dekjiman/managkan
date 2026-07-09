<?php

namespace Module\Integration\Models;

use CodeIgniter\Model;

class IntegrationModel extends Model
{
    protected $table = 'workspace_integrations';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'integrationId', 'workspaceId', 'connected', 'config',
        'connectedBy', 'connectedAt', 'updatedAt',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $returnType = 'object';
}
