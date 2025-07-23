<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssessmentMaterial extends Model
{
    use HasUuids;

    protected $fillable = [
        'assessment_id',
        'materialable_id',
        'materialable_type',
        'order'
    ];

    protected $with = ['materialable'];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function materialable()
    {
        return $this->morphTo();
    }
}
