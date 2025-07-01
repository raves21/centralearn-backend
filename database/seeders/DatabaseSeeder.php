<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Instructor;
use App\Models\Program;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class
        ]);


        //superadmin
        $superAdmin = User::factory()->create([
            'email' => 'super@celms.com'
        ]);
        $superAdmin->assignRole(Role::SUPERADMIN);

        //admin
        $admin = User::factory()->create([
            'email' => 'admin@celms.com'
        ]);
        $admin->assignRole(Role::ADMIN);

        //departments
        $cea = Department::create([
            'name' => 'College of Engineering and Architecture',
            'code' => 'CEA'
        ]);
        $ccis = Department::create([
            'name' => 'College of Computer and Information Science',
            'code' => 'CCIS'
        ]);
        $atycb = Department::create([
            'name' => 'Alfonso T. Yuchengco College of Business',
            'code' => 'ATYCB'
        ]);
        $cas = Department::create([
            'name' => 'College of Art and Sciences',
            'code' => 'CAS'
        ]);

        //programs
        $civilEng = Program::create([
            'department_id' => $cea->id,
            'name' => 'Bachelor of Science in Civil Engineering',
            'code' => 'CE'
        ]);
        $comSci = Program::create([
            'department_id' => $ccis->id,
            'name' => 'Bachelor of Science in Computer Science',
            'code' => 'CS'
        ]);
        $businessAd = Program::create([
            'department_id' => $atycb->id,
            'name' => 'Bachelor of Science in Business Administration',
            'code' => 'BA'
        ]);
        $mma = Program::create([
            'department_id' => $cas->id,
            'name' => 'Bachelor of Arts in Multimedia Arts',
            'code' => 'MMA'
        ]);


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
            'department_id' => $atycb->id,
            'job_title' => 'Professor'
        ]);

        $instructor_user_1->assignRole(Role::INSTRUCTOR);
        $instructor_user_2->syncRoles([Role::INSTRUCTOR, Role::ADMIN]);
        $instructor_user_3->assignRole(Role::INSTRUCTOR);
        $instructor_user_4->assignRole(Role::INSTRUCTOR);


        //4 students
        $student1 = User::factory()->create([
            'email' => 'student1@celms.com'
        ]);
        Student::create([
            'user_id' => $student1->id,
            'program_id' => $civilEng->id
        ]);

        $student2 = User::factory()->create([
            'email' => 'student2@celms.com'
        ]);
        Student::create([
            'user_id' => $student2->id,
            'program_id' => $comSci->id
        ]);

        $student3 = User::factory()->create([
            'email' => 'student3@celms.com'
        ]);
        Student::create([
            'user_id' => $student3->id,
            'program_id' => $businessAd->id
        ]);

        $student4 = User::factory()->create([
            'email' => 'student4@celms.com'
        ]);
        Student::create([
            'user_id' => $student4->id,
            'program_id' => $mma->id
        ]);

        $student1->assignRole(Role::STUDENT);
        $student2->assignRole(Role::STUDENT);
        $student3->assignRole(Role::STUDENT);
        $student4->assignRole(Role::STUDENT);
    }
}
