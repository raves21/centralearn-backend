<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\CourseClass;
use Illuminate\Database\Seeder;

class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Chapter::create([
            'course_class_id' => CourseClass::first()->id,
            'name' => 'Module 1',
            'description' => 'This is the first module.',
            'order' => 1,
            'published_at' => now(),
            'course_class_id' => CourseClass::first()->id,
        ]);
    }
}
