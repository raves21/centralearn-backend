<?php

namespace App\Http\Repositories;

use App\Models\OptionBaseditem;

class OptionBasedItemRepository extends BaseRepository
{
    public function __construct(OptionBasedItem $optionBasedItem)
    {
        parent::__construct($optionBasedItem);
    }
}
