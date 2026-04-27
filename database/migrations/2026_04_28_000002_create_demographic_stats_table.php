<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('demographic_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('population')->nullable();
            $table->decimal('poverty_rate', 8, 2)->nullable();
            $table->decimal('median_income', 12, 2)->nullable();
            $table->decimal('education_gap_rate', 8, 2)->nullable();
            $table->decimal('youth_unemployment_rate', 8, 2)->nullable();
            $table->decimal('religious_access_gap_rate', 8, 2)->nullable();
            $table->string('source_name')->nullable();
            $table->text('source_url')->nullable();
            $table->timestamps();

            $table->unique(['district_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demographic_stats');
    }
};
