<?php

namespace App\Http\Repositories;

use App\Models\OptionBasedItem;

class OptionBasedItemRepository extends BaseRepository
{
    public function __construct(OptionBasedItem $optionBasedItem)
    {
        parent::__construct($optionBasedItem);
    }
}
