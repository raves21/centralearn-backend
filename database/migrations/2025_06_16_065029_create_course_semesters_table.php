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
        Schema::create('course_semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('semester_id')->constrained()->cascadeOnDelete();
            $table->string('image_url')->nullable();
            $table->string('section_name')->nullable();
            $table->enum('status', ['open', 'close']);
            $table->timestamps();

            $table->unique(['course_id', 'semester_id', 'section_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_semesters');
    }
};
