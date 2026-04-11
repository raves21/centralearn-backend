<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AssessmentSubmissionSettings extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
