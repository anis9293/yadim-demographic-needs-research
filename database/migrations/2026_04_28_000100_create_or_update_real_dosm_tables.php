<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->string('state');
                $table->string('name');
                $table->string('slug')->nullable()->index();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamps();
                $table->unique(['state', 'name']);
            });
        } else {
            Schema::table('districts', function (Blueprint $table) {
                if (!Schema::hasColumn('districts', 'code')) $table->string('code')->nullable()->unique()->after('id');
                if (!Schema::hasColumn('districts', 'state')) $table->string('state')->nullable()->after('id');
                if (!Schema::hasColumn('districts', 'name')) $table->string('name')->nullable()->after('state');
                if (!Schema::hasColumn('districts', 'slug')) $table->string('slug')->nullable()->index()->after('name');
                if (!Schema::hasColumn('districts', 'latitude')) $table->decimal('latitude', 10, 7)->nullable();
                if (!Schema::hasColumn('districts', 'longitude')) $table->decimal('longitude', 10, 7)->nullable();
            });
        }

        if (!Schema::hasTable('district_demographics')) {
            Schema::create('district_demographics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
                $table->integer('source_year')->index();
                $table->unsignedBigInteger('population_total')->nullable();
                $table->decimal('youth_share', 8, 2)->nullable();
                $table->decimal('income_median', 12, 2)->nullable();
                $table->decimal('income_mean', 12, 2)->nullable();
                $table->decimal('poverty_rate', 8, 2)->nullable();
                $table->decimal('gini', 8, 3)->nullable();
                $table->decimal('expenditure_mean', 12, 2)->nullable();
                $table->date('population_source_date')->nullable();
                $table->date('hies_source_date')->nullable();
                $table->timestamps();
                $table->unique(['district_id', 'source_year']);
            });
        }

        if (!Schema::hasTable('community_need_scores')) {
            Schema::create('community_need_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
                $table->decimal('poverty_score', 8, 2)->default(0);
                $table->decimal('education_gap_score', 8, 2)->default(0);
                $table->decimal('youth_risk_score', 8, 2)->default(0);
                $table->decimal('religious_access_gap_score', 8, 2)->default(0);
                $table->decimal('cni_score', 8, 2)->default(0)->index();
                $table->string('method_version')->default('opendosm_realdata_v1');
                $table->timestamps();
                $table->unique('district_id');
            });
        } else {
            Schema::table('community_need_scores', function (Blueprint $table) {
                if (!Schema::hasColumn('community_need_scores', 'education_gap_score')) {
                    $table->decimal('education_gap_score', 8, 2)->default(0)->after('poverty_score');
                }
                if (!Schema::hasColumn('community_need_scores', 'religious_access_gap_score')) {
                    $table->decimal('religious_access_gap_score', 8, 2)->default(0)->after('youth_risk_score');
                }
                if (!Schema::hasColumn('community_need_scores', 'method_version')) {
                    $table->string('method_version')->default('opendosm_realdata_v1')->after('cni_score');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_need_scores');
        Schema::dropIfExists('district_demographics');
        Schema::dropIfExists('districts');
    }
};
