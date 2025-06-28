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
        Schema::create('text_based_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('question_text');
            $table->enum('type', ['essay', 'identification'])->default('essay');
            $table->string('identification_answer')->nullable();
            $table->boolean('is_identification_answer_case_sensitive')->nullable();
            $table->integer('point_worth');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('text_based_questions');
    }
};
