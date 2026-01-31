<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class LectureMaterial extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $with = ['materialable'];

    public function lecture()
    {
        return $this->belongsTo(Lecture::class);
    }

    public function materialable()
    {
        return $this->morphTo();
    }
}
