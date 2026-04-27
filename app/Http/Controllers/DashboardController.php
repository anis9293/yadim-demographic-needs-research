<?php

namespace App\Http\Controllers;

use App\Services\DosmRealDataImportService;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardController extends Controller
{
    public function index(DosmRealDataImportService $dosmImporter)
    {
        $openDosmStatus = $this->refreshOpenDosmData($dosmImporter);
        $districts = $this->districtQuery()->get();

        return view('dashboard', compact('districts', 'openDosmStatus'));
    }

    public function districtData(DosmRealDataImportService $dosmImporter)
    {
        $this->refreshOpenDosmData($dosmImporter);

        return response()->json($this->districtQuery()->get());
    }

    private function districtQuery()
    {
        return DB::table('districts')
            ->leftJoin('district_demographics', 'districts.id', '=', 'district_demographics.district_id')
            ->leftJoin('community_need_scores', 'districts.id', '=', 'community_need_scores.district_id')
            ->select(
                'districts.id', 'districts.state', 'districts.name', 'districts.latitude', 'districts.longitude',
                'district_demographics.population_total', 'district_demographics.youth_share',
                'district_demographics.income_median', 'district_demographics.income_mean',
                'district_demographics.poverty_rate', 'district_demographics.gini',
                'district_demographics.population_source_date', 'district_demographics.hies_source_date',
                'community_need_scores.poverty_score', 'community_need_scores.education_gap_score',
                'community_need_scores.youth_risk_score', 'community_need_scores.religious_access_gap_score',
                'community_need_scores.cni_score', 'community_need_scores.method_version'
            )
            ->orderByDesc('community_need_scores.cni_score');
    }

    private function refreshOpenDosmData(DosmRealDataImportService $dosmImporter): array
    {
        if (app()->environment('testing')) {
            return [
                'ok' => true,
                'skipped' => true,
            ];
        }

        try {
            return [
                'ok' => true,
                'result' => $dosmImporter->import(),
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }
}
