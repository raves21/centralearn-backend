<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class QuestionOption extends Model
{
    use HasUuids;

    protected $fillable = [
        'option_based_question_id',
        'optionable_id',
        'optionable_type',
        'is_correct',
        'order'
    ];

    protected $with = ['optionable'];

    public function optionBasedQuestion()
    {
        return $this->belongsTo(OptionBasedQuestion::class);
    }

    public function optionable()
    {
        return $this->morphTo();
    }
}
