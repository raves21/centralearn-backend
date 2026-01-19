<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class TextAttachment extends Model
{
    use HasUuids;

    protected $fillable = [
        'content'
    ];

    public function lectureMaterial()
    {
        return $this->morphOne(LectureMaterial::class, 'materialable');
    }

    public function assessmentMaterial()
    {
        return $this->morphOne(AssessmentMaterial::class, 'materialable');
    }

    public function optionBasedItemOption()
    {
        return $this->morphOne(OptionBasedItemOption::class, 'optionable');
    }
}
