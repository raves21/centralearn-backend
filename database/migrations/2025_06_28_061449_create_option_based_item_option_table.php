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
        Schema::create('option_based_item_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('option_based_item_id')->constrained()->cascadeOnDelete();
            $table->string('option_text')->nullable();
            $table->json('option_file')->nullable();
            $table->integer('order');
            $table->boolean('is_correct');
            $table->timestamps();

            $table->unique(['order', 'option_based_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_based_item_options');
    }
};
