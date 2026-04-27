<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('community_need_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('poverty_score', 8, 2)->default(0);
            $table->decimal('education_score', 8, 2)->default(0);
            $table->decimal('youth_risk_score', 8, 2)->default(0);
            $table->decimal('religious_access_score', 8, 2)->default(0);
            $table->decimal('cni_score', 8, 2)->default(0);
            $table->string('priority_level')->default('Low');
            $table->json('recommended_actions')->nullable();
            $table->timestamps();

            $table->unique(['district_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_need_scores');
    }
};
