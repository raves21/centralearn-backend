<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class Department extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'code'
    ];

    protected static function booted()
    {
        static::created(function ($department) {
            if (isset($department->image_path)) {
                Storage::disk('public')->deleteDirectory("departments/{$department->code}");
                Storage::disk('public')->makeDirectory("departments/{$department->code}");

                $fileName = $department->image_path;
                $targetPath = "departments/{$department->code}/{$fileName}";

                Storage::disk('public')->move($fileName, $targetPath);

                $department->update([
                    'image_path' => $targetPath
                ]);
            }
        });

        static::deleted(function ($department) {
            if (isset($department->image_path)) {
                Storage::disk('public')->deleteDirectory("departments/{$department->code}");
            }
        });
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }
}
