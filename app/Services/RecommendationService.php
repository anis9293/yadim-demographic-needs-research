<?php

namespace App\Services;

use App\Models\DemographicStat;

class RecommendationService
{
    public function recommend(DemographicStat $stat): array
    {
        $actions = [];

        if (($stat->poverty_rate ?? 0) >= 8) {
            $actions[] = 'Food basket / basic needs CSR';
            $actions[] = 'Zakat and financial aid screening';
        }

        if (($stat->education_gap_rate ?? 0) >= 25) {
            $actions[] = 'Community learning hub';
            $actions[] = 'Basic Islamic education exposure programme';
        }

        if (($stat->youth_unemployment_rate ?? 0) >= 10) {
            $actions[] = 'Youth skills and career programme';
            $actions[] = 'Spiritual resilience and counselling session';
        }

        if (($stat->religious_access_gap_rate ?? 0) >= 25) {
            $actions[] = 'Mobile dakwah outreach';
            $actions[] = 'Muallaf follow-up and support circle';
        }

        return array_values(array_unique($actions ?: [
            'Maintain periodic community engagement',
            'Monitor indicators for emerging needs',
        ]));
    }
}
