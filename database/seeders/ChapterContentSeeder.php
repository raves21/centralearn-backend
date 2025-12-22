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
            'is_published' => true,
            'publishes_at' => null,
            'contentable_id' => Lecture::first()->id,
            'contentable_type' => Lecture::class,
            'is_open' => true,
            'opens_at' => null,
            'closes_at' => null,
            'description' => null,
            'order' => 1
        ]);
    }
}
