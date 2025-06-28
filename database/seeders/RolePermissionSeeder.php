<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{

    public function run(): void
    {
        //superadmin has access to all permissions (check AppServiceProvider.php)
        $allRoles = [Role::ADMIN, Role::SUPERADMIN, Role::STUDENT, Role::INSTRUCTOR];
        $superadminOnly = [Role::SUPERADMIN];
        $adminOnly = [Role::ADMIN];
        $instructorAndAdmin = [Role::ADMIN, Role::INSTRUCTOR];
        $studentAndAdmin = [Role::STUDENT, Role::ADMIN];
        $studentAndInstructor = [Role::STUDENT, Role::INSTRUCTOR];

        $permissionsAssignedToRoles = [

            //admin
            'create admin' => $superadminOnly,
            'update admin' => $superadminOnly,
            'view admin' => $adminOnly,
            'delete admin' => $superadminOnly,

            //student
            'create student' => $adminOnly,
            'update student' => $adminOnly,
            'delete student' => $adminOnly,
            'view student' => $allRoles,
            'add student to course' => $adminOnly,
            'remove student from course' => $adminOnly,

            //instructor
            'create instructor' => $adminOnly,
            'update instructor' => $adminOnly,
            'delete instructor' => $adminOnly,
            'view instructor' => $allRoles,
            'add instructor to course' => $adminOnly,
            'remove instructor from course' => $adminOnly,

            //course_student
            'update student final grade in a course' => $adminOnly,

            //course
            'create course' => $adminOnly,
            'update course' => $adminOnly,
            'delete course' => $adminOnly,
            'view course' => $allRoles,
            'add course to department' => $adminOnly,
            'remove course from department' => $adminOnly,

            //course chapter
            'create chapter' => $instructorAndAdmin,
            'update chapter' => $instructorAndAdmin,
            'delete chapter' => $instructorAndAdmin,
            'view chapter' => $instructorAndAdmin,

            //chapter content
            'create chapter content' => $instructorAndAdmin,
            'update chapter content' => $instructorAndAdmin,
            'delete chapter content' => $instructorAndAdmin,
            'view chapter content' => $allRoles,

            //term
            'create term' => $superadminOnly,
            'update term' => $superadminOnly,
            'delete term' => $superadminOnly,
            'view term' => $allRoles,

            //department
            'create department' => $superadminOnly,
            'update department' => $superadminOnly,
            'delete department' => $superadminOnly,
            'view department' => $allRoles,

            //program
            'create program' => $superadminOnly,
            'update program' => $superadminOnly,
            'delete program' => $superadminOnly,
            'view program' => $allRoles,

            //assessment_submission
            'create assessment_submission' => $studentAndAdmin,
            'update assessment_submission' => $instructorAndAdmin,
            'delete assessment_submission' => $adminOnly,
            'view assessment_submission' => $allRoles,

        ];

        //create roles
        foreach ($allRoles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        //grant permission to roles
        foreach ($permissionsAssignedToRoles as $permission => $roles) {
            //create permission
            $permission = Permission::firstOrCreate(['name' => $permission]);
            //grant permission to roles
            $permission->syncRoles($roles);
        }
    }
}
