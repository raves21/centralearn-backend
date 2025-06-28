<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
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


        //3 instructors
        $instructor_user_1 = User::factory()->create([
            'email' => 'instructor1@celms.com'
        ]);
        $instructor_user_2 = User::factory()->create([
            'email' => 'instructor2@celms.com'
        ]);
        $instructor_user_3 = User::factory()->create([
            'email' => 'instructor3@celms.com'
        ]);

        $instructor_user_1->assignRole(Role::INSTRUCTOR);
        $instructor_user_2->assignRole(Role::INSTRUCTOR);
        $instructor_user_3->assignRole(Role::INSTRUCTOR);



        //3 students
        $student1 = User::factory()->create([
            'email' => 'student1@celms.com'
        ]);
        $student2 = User::factory()->create([
            'email' => 'student2@celms.com'
        ]);
        $student3 = User::factory()->create([
            'email' => 'student3@celms.com'
        ]);

        $student1->assignRole(Role::STUDENT);
        $student2->assignRole(Role::STUDENT);
        $student3->assignRole(Role::STUDENT);
    }
}
