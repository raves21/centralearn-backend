<?php

namespace App\Http\Repositories;

use App\Models\TextAttachment;

class TextAttachmentRepository extends BaseRepository
{
    public function __construct(TextAttachment $textAttachment)
    {
        parent::__construct($textAttachment);
    }
}