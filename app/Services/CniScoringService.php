<?php

namespace App\Services;

use App\Models\CommunityNeedScore;
use App\Models\DemographicStat;

class CniScoringService
{
    public function __construct(private RecommendationService $recommendationService) {}

    public function calculateAndSave(DemographicStat $stat): CommunityNeedScore
    {
        $povertyScore = $this->normalize($stat->poverty_rate, 0, 20);
        $educationScore = $this->normalize($stat->education_gap_rate, 0, 60);
        $youthRiskScore = $this->normalize($stat->youth_unemployment_rate, 0, 25);
        $religiousAccessScore = $this->normalize($stat->religious_access_gap_rate, 0, 60);

        $cni = round(
            ($povertyScore * 0.35) +
            ($educationScore * 0.25) +
            ($youthRiskScore * 0.20) +
            ($religiousAccessScore * 0.20),
            2
        );

        return CommunityNeedScore::updateOrCreate(
            ['district_id' => $stat->district_id, 'year' => $stat->year],
            [
                'poverty_score' => $povertyScore,
                'education_score' => $educationScore,
                'youth_risk_score' => $youthRiskScore,
                'religious_access_score' => $religiousAccessScore,
                'cni_score' => $cni,
                'priority_level' => $this->priorityLevel($cni),
                'recommended_actions' => $this->recommendationService->recommend($stat),
            ]
        );
    }

    private function normalize(?float $value, float $min, float $max): float
    {
        if ($value === null || $max <= $min) {
            return 0;
        }

        $score = (($value - $min) / ($max - $min)) * 100;
        return round(max(0, min(100, $score)), 2);
    }

    private function priorityLevel(float $score): string
    {
        return match (true) {
            $score >= 70 => 'Critical',
            $score >= 50 => 'High',
            $score >= 30 => 'Medium',
            default => 'Low',
        };
    }
}
