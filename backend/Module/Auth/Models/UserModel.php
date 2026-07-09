<?php

namespace Module\Auth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'id', 'name', 'email', 'emailVerified', 'image', 'stripeCustomerId', 'createdAt', 'updatedAt',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'createdAt';
    protected $updatedField = 'updatedAt';
    protected $deletedField = 'deletedAt';
    protected $returnType = 'object';
}
