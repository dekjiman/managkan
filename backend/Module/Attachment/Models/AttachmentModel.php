<?php

namespace Module\Attachment\Models;

use CodeIgniter\Model;

class AttachmentModel extends Model
{
    protected $table = 'card_attachment';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id', 'publicId', 'cardId',
        'filename', 'originalFilename', 'contentType', 'size', 'path',
        'createdBy', 'createdAt', 'deletedAt',
    ];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $returnType = 'object';
}
