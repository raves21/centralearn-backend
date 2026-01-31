<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class OptionBasedItemOption extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'option_file' => 'array',
        'is_correct' => 'boolean',
    ];

    public function optionBasedItem()
    {
        return $this->belongsTo(OptionBasedItem::class);
    }
}
