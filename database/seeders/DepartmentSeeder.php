<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //departments
        Department::create([
            'name' => 'College of Engineering and Architecture',
            'code' => 'CEA'
        ]);
        Department::create([
            'name' => 'College of Computer and Information Science',
            'code' => 'CCIS'
        ]);
        Department::create([
            'name' => 'College of Business',
            'code' => 'CB'
        ]);
        Department::create([
            'name' => 'College of Art and Sciences',
            'code' => 'CAS'
        ]);
    }
}
