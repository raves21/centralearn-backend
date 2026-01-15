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
        Schema::create('essay_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('question_text');
            $table->integer('point_worth');
            $table->integer('min_character_count')->nullable();
            $table->integer('max_character_count')->nullable();
            $table->integer('min_word_count')->nullable();
            $table->integer('max_word_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('essay_questions');
    }
};
