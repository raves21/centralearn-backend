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
        Schema::create('question_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('option_based_question_id')->constrained()->cascadeOnDelete();
            $table->string('optionable_type');
            $table->string('optionable_id');
            $table->integer('order');
            $table->boolean('is_correct');
            $table->timestamps();

            $table->unique(['order', 'option_based_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
