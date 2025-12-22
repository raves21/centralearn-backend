<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::create([
            'name' => 'Life and Works of Jose Rizal',
            'description' => 'A beginner course on the life and works of Jose Rizal.',
            'code' => 'RIZAL101',
        ]);

        $departmentIds = Department::pluck('id')->toArray();
        $course->departments()->sync($departmentIds);
    }
}
