<?php

namespace App\Http\Controllers;

use App\Models\CommunityNeedScore;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $states = District::query()->distinct()->orderBy('state')->pluck('state');
        return view('dashboard.index', compact('states'));
    }

    public function geojson(): JsonResponse
    {
        $scores = CommunityNeedScore::query()
            ->with('district')
            ->latest('year')
            ->get();

        $features = $scores->map(function (CommunityNeedScore $score) {
            $district = $score->district;

            return [
                'type' => 'Feature',
                'geometry' => $district->geometry_geojson ?: [
                    'type' => 'Point',
                    'coordinates' => [(float) $district->longitude, (float) $district->latitude],
                ],
                'properties' => [
                    'district_id' => $district->id,
                    'district' => $district->name,
                    'state' => $district->state,
                    'year' => $score->year,
                    'cni_score' => (float) $score->cni_score,
                    'priority_level' => $score->priority_level,
                    'recommended_actions' => $score->recommended_actions ?? [],
                    'poverty_score' => (float) $score->poverty_score,
                    'education_score' => (float) $score->education_score,
                    'youth_risk_score' => (float) $score->youth_risk_score,
                    'religious_access_score' => (float) $score->religious_access_score,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
