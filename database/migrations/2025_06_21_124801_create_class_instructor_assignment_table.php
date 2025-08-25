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
        Schema::create('class_instructor_assignment', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('course_class_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('instructor_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_instructor_assignment');
    }
};
