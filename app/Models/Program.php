<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;
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

    protected function handleImageUpload(string $imagePath)
    {
        Storage::disk('public')->deleteDirectory("programs/{$this->code}");
        Storage::disk('public')->makeDirectory("programs/{$this->code}");
        $finalImagePath = "programs/{$this->code}/{$imagePath}";
        Storage::disk('public')->move($imagePath, $finalImagePath);

        return $finalImagePath;
    }

    protected static function booted()
    {
        static::creating(function ($program) {
            if (isset($program->image_path)) {
                $program->image_path = $program->handleImageUpload($program->image_path);
            }
        });

        static::updating(function ($program) {
            $new = $program->attributes;

            if (empty($new['image_path'])) {
                Storage::disk('public')->deleteDirectory("programs/{$program->code}");
            } else {
                $program->image_path = $program->handleImageUpload($new['image_path']);
            }
        });

        static::deleted(function ($program) {
            if (isset($program->image_path)) {
                Storage::disk('public')->deleteDirectory("programs/{$program->code}");
            }
        });
    }
}
