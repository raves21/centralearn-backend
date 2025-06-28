<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use PDO;

class Program extends Model
{
    use HasUuids;

    protected $fillable = [
        'department_id',
        'name',
        'description',
        'image_path',
        'code'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
