<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //programs
        $civilEng = Program::where('code', 'CE')->first();
        $comSci = Program::where('code', 'CS')->first();
        $businessAd = Program::where('code', 'BA')->first();
        $mma = Program::where('code', 'MMA')->first();

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
