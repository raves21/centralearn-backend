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
        Schema::create('student_assessment_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('assessment_version_id')->constrained()->cascadeOnDelete();
            $table->json('answers'); //raw answers
            $table->integer('attempt_number');
            $table->enum('status', ['ongoing', 'submitted']);
            $table->timestamp('started_at');

            //contains the questionnaire with student's answers and point/s earned per item and whether its correct/wrong
            //this should only be filled if the attempt has been submitted
            $table->json('submission_summary')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->double('total_score')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'assessment_version_id', 'attempt_number'], 'stu_assess_ver_attempt_uniq');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assessment_attempts');
    }
};
