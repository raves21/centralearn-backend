<?php

namespace App\Http\Repositories;

use App\Models\OptionBasedItemOption;

class OptionBasedItemOptionRepository extends BaseRepository
{
    public function __construct(OptionBasedItemOption $optionBasedItemOption)
    {
        parent::__construct($optionBasedItemOption);
    }
}
