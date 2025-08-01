<?php

namespace App\Http\Repositories;

use App\Models\FileAttachment;

class FileAttachmentRepository extends BaseRepository
{
    public function __construct(FileAttachment $fileAttachment)
    {
        parent::__construct($fileAttachment);
    }
}