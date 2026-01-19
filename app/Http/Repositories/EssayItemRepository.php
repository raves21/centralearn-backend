<?php

namespace App\Http\Repositories;

use App\Models\EssayItem;

class EssayItemRepository extends BaseRepository
{
    public function __construct(EssayItem $essayItem)
    {
        parent::__construct($essayItem);
    }
}
