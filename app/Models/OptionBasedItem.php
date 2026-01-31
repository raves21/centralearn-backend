<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class OptionBasedItem extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $with = ['optionBasedItemOptions'];

    public function optionBasedItemOptions()
    {
        return $this->hasMany(OptionBasedItemOption::class);
    }
}
