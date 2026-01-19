<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class OptionBasedItemOption extends Model
{
    use HasUuids;

    protected $fillable = [
        'option_based_item_id',
        'option_text',
        'option_file_url',
        'is_correct',
        'order',
    ];

    public function optionBasedItem()
    {
        return $this->belongsTo(OptionBasedItem::class);
    }
}
