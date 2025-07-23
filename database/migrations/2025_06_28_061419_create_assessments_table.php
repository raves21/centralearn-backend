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
        Schema::create('assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('is_open');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->integer('time_limit')->nullable();
            $table->float('max_score');
            $table->boolean('is_answers_viewable_after_submit');
            $table->boolean('is_score_viewable_after_submit');
            $table->boolean('is_multi_attempts')->boolean();
            $table->integer('max_attempts')->nullable();
            $table->enum('multi_attempt_grading_type', ['avg_score', 'highest_score'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
