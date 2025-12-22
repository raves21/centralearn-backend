<?php

namespace Database\Seeders;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Semester::create([
            'name' => 'Default Semester',
            'start_date' => Carbon::now()->subMonth(2),
            'end_date' => Carbon::now()->addMonth(2),
        ]);
    }
}
