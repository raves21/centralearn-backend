<?php

namespace App\Models;

use App\Observers\DepartmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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

    protected function handleImageUpload(string $imagePath)
    {
        Storage::disk('public')->deleteDirectory("departments/{$this->code}");
        Storage::disk('public')->makeDirectory("departments/{$this->code}");
        $finalImagePath = "departments/{$this->code}/{$imagePath}";
        Storage::disk('public')->move($imagePath, $finalImagePath);

        return $finalImagePath;
    }

    protected static function booted()
    {
        static::creating(function ($department) {
            if (isset($department->image_path)) {
                $department->image_path = $department->handleImageUpload($department->image_path);
            }
        });

        static::updating(function ($department) {
            $new = $department->attributes;

            if (empty($new['image_path'])) {
                Storage::disk('public')->deleteDirectory("departments/{$department->code}");
            } else {
                $department->image_path = $department->handleImageUpload($new['image_path']);
            }
        });

        static::deleted(function ($department) {
            if (isset($department->image_path)) {
                Storage::disk('public')->deleteDirectory("departments/{$department->code}");
            }
        });
    }
}
