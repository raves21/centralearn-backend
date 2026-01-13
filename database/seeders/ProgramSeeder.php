<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
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

        //programs
        Program::create([
            'department_id' => $cea->id,
            'name' => 'Bachelor of Science in Civil Engineering',
            'code' => 'CE'
        ]);
        Program::create([
            'department_id' => $ccis->id,
            'name' => 'Bachelor of Science in Computer Science',
            'code' => 'CS'
        ]);
        Program::create([
            'department_id' => $cb->id,
            'name' => 'Bachelor of Science in Business Administration',
            'code' => 'BA'
        ]);
        Program::create([
            'department_id' => $cas->id,
            'name' => 'Bachelor of Arts in Multimedia Arts',
            'code' => 'MMA'
        ]);
    }
}
