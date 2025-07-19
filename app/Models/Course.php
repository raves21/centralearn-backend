<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    use HasUuids;

    protected $fillable = ['description', 'code', 'image_path', 'name'];

    public function studentEnrollments()
    {
        return $this->hasMany(CourseStudentEnrollment::class);
    }

    public function instructorAssignments()
    {
        return $this->hasMany(CourseInstructorAssignment::class);
    }

    public function instructors()
    {
        return $this->hasManyThrough(
            Instructor::class,
            CourseInstructorAssignment::class,
            'course_id',
            'id',
            'id',
            'instructor_id'
        );
    }

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            CourseStudentEnrollment::class,
            'course_id',
            'id',
            'id',
            'student_id'
        );
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function chapters()
    {
        return $this->hasMany(CourseChapter::class);
    }

    protected function handleImageUpload(string $imagePath)
    {
        Storage::disk('public')->deleteDirectory("courses/{$this->code}");
        Storage::disk('public')->makeDirectory("courses/{$this->code}");
        $finalImagePath = "courses/{$this->code}/{$imagePath}";
        Storage::disk('public')->move($imagePath, $finalImagePath);

        return $finalImagePath;
    }

    protected static function booted()
    {
        static::creating(function ($course) {
            if (isset($course->image_path)) {
                $course->image_path = $course->handleImageUpload($course->image_path);
            }
        });

        static::updating(function ($course) {
            $new = $course->attributes;

            if (empty($new['image_path'])) {
                Storage::disk('public')->deleteDirectory("courses/{$course->code}");
            } else {
                $course->image_path = $course->handleImageUpload($new['image_path']);
            }
        });

        static::deleted(function ($course) {
            if (isset($course->image_path)) {
                Storage::disk('public')->deleteDirectory("courses/{$course->code}");
            }
        });
    }
}
