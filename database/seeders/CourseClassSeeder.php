<?php

namespace Database\Seeders;

use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Services\FileAttachmentService;
use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseClass;
use App\Models\Section;
use App\Models\Semester;

class CourseClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CourseClass::create([
            'course_id' => Course::first()->id,
            'section_id' => Section::first()->id,
            'semester_id' => Semester::first()->id,
            'status' => 'open',
            'image_url' => FileAttachmentService::getRandomDefaultImageUrl()
        ]);
    }
}
