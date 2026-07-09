<?php

namespace Module\Card\Models;

use CodeIgniter\Model;

class CardLabelModel extends Model
{
    protected $table = '_card_labels';
    protected $primaryKey = null;
    protected $allowedFields = [
        'cardId',
        'labelId',
    ];
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $returnType = 'object';
}
