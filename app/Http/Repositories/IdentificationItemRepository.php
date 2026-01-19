<?php

namespace App\Http\Repositories;

use App\Models\IdentificationItem;

class IdentificationItemRepository extends BaseRepository
{
    public function __construct(IdentificationItem $identificationItem)
    {
        parent::__construct($identificationItem);
    }
}