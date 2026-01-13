<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminSeeder::class,
            SemesterSeeder::class,
            DepartmentSeeder::class,
            ProgramSeeder::class,
            InstructorSeeder::class,
            StudentSeeder::class,
            SectionSeeder::class,
            CourseSeeder::class,
            CourseClassSeeder::class,
            ChapterSeeder::class,
            LectureSeeder::class,
            ChapterContentSeeder::class,
        ]);
    }
}
