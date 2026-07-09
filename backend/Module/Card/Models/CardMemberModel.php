<?php

namespace Module\Card\Models;

use CodeIgniter\Model;

class CardMemberModel extends Model
{
    protected $table = '_card_workspace_members';
    protected $primaryKey = null;
    protected $allowedFields = [
        'cardId',
        'workspaceMemberId',
    ];
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $returnType = 'object';
}
