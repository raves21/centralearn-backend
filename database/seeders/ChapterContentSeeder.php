<?php

namespace Database\Seeders;

use App\Models\ChapterContent;
use App\Models\Chapter;
use App\Models\Lecture;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChapterContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChapterContent::create([
            'chapter_id' => Chapter::first()->id,
            'name' => 'Who is Jose Rizal?',
            'accessibility_settings' => [
                'visible' => true,
                'custom' => null
            ],
            'contentable_id' => Lecture::first()->id,
            'contentable_type' => Lecture::class,
            'description' => null,
            'order' => 1
        ]);
    }
}
