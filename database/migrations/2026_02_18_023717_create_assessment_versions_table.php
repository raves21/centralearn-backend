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
        Schema::create('assessment_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assessment_id')->constrained();
            $table->integer('version_number')->default(1);
            $table->json('questionnaire_snapshot')->nullable(); //json array of assessmentMaterials (without the answers)
            $table->json('answer_key')->nullable(); //json array of answer keys of questionnaire
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_versions');
    }
};
