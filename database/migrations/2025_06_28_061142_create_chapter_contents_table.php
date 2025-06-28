<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chapter_contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_chapter_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_published');
            $table->timestamp('publishes_at')->nullable();
            $table->morphs('contentable');
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapter_contents');
    }
};
