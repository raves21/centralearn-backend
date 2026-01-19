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
        Schema::create('assessment_material_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('assessment_material_id')->constrained()->cascadeOnDelete();
            $table->string('question_text')->nullable();
            $table->json('question_file_urls')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_material_questions');
    }
};
