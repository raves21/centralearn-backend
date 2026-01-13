<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //superadmin
        $superAdmin = User::factory()->create([
            'email' => 'super@celms.com'
        ]);
        $superAdmin->assignRole(Role::SUPERADMIN);

        //admin
        $admin = User::factory()->create([
            'email' => 'admin@celms.com'
        ]);
        Admin::create([
            'user_id' => $admin->id,
            'job_title' => 'University Admin 1'
        ]);
        $admin->assignRole(Role::ADMIN);
    }
}
