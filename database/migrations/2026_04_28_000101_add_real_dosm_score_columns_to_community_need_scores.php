<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('community_need_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('community_need_scores', 'education_gap_score')) {
                $table->decimal('education_gap_score', 8, 2)->default(0)->after('poverty_score');
            }

            if (!Schema::hasColumn('community_need_scores', 'religious_access_gap_score')) {
                $table->decimal('religious_access_gap_score', 8, 2)->default(0)->after('youth_risk_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_need_scores', function (Blueprint $table) {
            if (Schema::hasColumn('community_need_scores', 'religious_access_gap_score')) {
                $table->dropColumn('religious_access_gap_score');
            }

            if (Schema::hasColumn('community_need_scores', 'education_gap_score')) {
                $table->dropColumn('education_gap_score');
            }
        });
    }
};
