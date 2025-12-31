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
            $table->foreignUuid('chapter_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamp('publishes_at')->nullable();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->string('contentable_type');
            $table->string('contentable_id');
            $table->integer('order');
            $table->timestamps();

            $table->unique(['order', 'chapter_id']);
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
