<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Instructor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //departments
        $cea = Department::find('code', 'CEA');
        $ccis = Department::find('code', 'CCIS');
        $cb = Department::find('code', 'CB');
        $cas = Department::find('code', 'CAS');

        //4 instructors
        $instructor_user_1 = User::factory()->create([
            'email' => 'instructor1@celms.com'
        ]);
        Instructor::create([
            'user_id' => $instructor_user_1->id,
            'department_id' => $cea->id,
            'job_title' => 'Professor'
        ]);

        $instructor_user_2 = User::factory()->create([
            'email' => 'instructor2@celms.com'
        ]);
        Instructor::create([
            'user_id' => $instructor_user_2->id,
            'department_id' => $ccis->id,
            'job_title' => 'College Dean'
        ]);

        $instructor_user_3 = User::factory()->create([
            'email' => 'instructor3@celms.com'
        ]);
        Instructor::create([
            'user_id' => $instructor_user_3->id,
            'department_id' => $cas->id,
            'job_title' => 'Professor'
        ]);

        $instructor_user_4 = User::factory()->create([
            'email' => 'instructor4@celms.com'
        ]);
        Instructor::create([
            'user_id' => $instructor_user_4->id,
            'department_id' => $cb->id,
            'job_title' => 'Professor'
        ]);

        $instructor_user_1->assignRole(Role::INSTRUCTOR);
        $instructor_user_2->assignRole(Role::INSTRUCTOR);
        $instructor_user_3->assignRole(Role::INSTRUCTOR);
        $instructor_user_4->assignRole(Role::INSTRUCTOR);
    }
}
