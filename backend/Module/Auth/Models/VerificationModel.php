<?php

namespace Module\Auth\Models;

use CodeIgniter\Model;

class VerificationModel extends Model
{
    protected $table = 'verification';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'id', 'identifier', 'value', 'expiresAt', 'createdAt', 'updatedAt',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'createdAt';
    protected $updatedField = 'updatedAt';
    protected $deletedField = 'deletedAt';
    protected $returnType = 'object';
}
