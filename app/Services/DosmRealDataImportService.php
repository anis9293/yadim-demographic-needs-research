<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DosmRealDataImportService
{
    private const OPENAPI_URL = 'https://api.data.gov.my/data-catalogue';
    private const POPULATION_DATASET = 'population_district';
    private const HIES_DATASET = 'hies_district';

    public function import(?string $state = null, ?int $limit = null, ?callable $logger = null): array
    {
        $log = $logger ?? fn (string $message) => null;

        $log('Fetching district population from OpenDOSM OpenAPI...');
        $populationRows = $this->downloadOpenApiDataset(self::POPULATION_DATASET, 500000);

        $log('Fetching HIES district income/poverty from OpenDOSM OpenAPI...');
        $hiesRows = $this->downloadOpenApiDataset(self::HIES_DATASET, 10000);

        $latestPopulationDate = collect($populationRows)->max('date');
        $latestHiesDate = collect($hiesRows)->max('date');

        $populationBase = collect($populationRows)
            ->filter(fn ($r) => ($r['date'] ?? null) === $latestPopulationDate)
            ->filter(fn ($r) => in_array($r['sex'] ?? null, ['overall', 'both'], true))
            ->filter(fn ($r) => ($r['ethnicity'] ?? null) === 'overall');

        if ($state) {
            $populationBase = $populationBase->filter(fn ($r) => Str::lower($r['state'] ?? '') === Str::lower($state));
        }

        $overallPopulation = $populationBase
            ->filter(fn ($r) => ($r['age'] ?? null) === 'overall')
            ->mapWithKeys(fn ($r) => [$this->key($r['state'], $r['district']) => $r]);

        $youthPopulation = $populationBase
            ->filter(fn ($r) => in_array($r['age'] ?? '', ['15-19', '20-24', '25-29'], true))
            ->groupBy(fn ($r) => $this->key($r['state'], $r['district']))
            ->map(fn ($rows) => collect($rows)->sum(fn ($r) => (float) $r['population']));

        $hies = collect($hiesRows)
            ->filter(fn ($r) => ($r['date'] ?? null) === $latestHiesDate)
            ->mapWithKeys(fn ($r) => [$this->key($r['state'], $r['district']) => $r]);

        $districtKeys = $overallPopulation->keys()->intersect($hies->keys())->values();
        if ($limit) {
            $districtKeys = $districtKeys->take($limit);
        }

        $rawMetrics = [];
        foreach ($districtKeys as $key) {
            $pop = $overallPopulation[$key];
            $h = $hies[$key];
            $populationTotal = ((float) $pop['population']) * 1000;
            $youthTotal = ((float) ($youthPopulation[$key] ?? 0)) * 1000;
            $rawMetrics[$key] = [
                'state' => $pop['state'],
                'district' => $this->canonicalDistrict($pop['state'], $pop['district']),
                'population_total' => $populationTotal,
                'youth_share' => $populationTotal > 0 ? ($youthTotal / $populationTotal) * 100 : 0,
                'income_median' => (float) ($h['income_median'] ?? 0),
                'income_mean' => (float) ($h['income_mean'] ?? 0),
                'poverty_rate' => (float) ($h['poverty'] ?? 0),
                'gini' => (float) ($h['gini'] ?? 0),
                'expenditure_mean' => (float) ($h['expenditure_mean'] ?? 0),
                'population_date' => $latestPopulationDate,
                'hies_date' => $latestHiesDate,
            ];
        }

        $incomeValues = collect($rawMetrics)->pluck('income_median')->filter(fn ($v) => $v > 0)->values();
        $povertyValues = collect($rawMetrics)->pluck('poverty_rate')->values();
        $youthValues = collect($rawMetrics)->pluck('youth_share')->values();
        $giniValues = collect($rawMetrics)->pluck('gini')->values();

        $inserted = 0;
        DB::transaction(function () use ($rawMetrics, $incomeValues, $povertyValues, $youthValues, $giniValues, &$inserted) {
            foreach ($rawMetrics as $row) {
                $povertyScore = $this->minMax($row['poverty_rate'], $povertyValues->min(), $povertyValues->max());
                $incomeGapScore = $this->inverseMinMax($row['income_median'], $incomeValues->min(), $incomeValues->max());
                $youthRiskScore = $this->minMax($row['youth_share'], $youthValues->min(), $youthValues->max());
                $inequalityScore = $this->minMax($row['gini'], $giniValues->min(), $giniValues->max());

                // Real-data CNI: all components below come from OpenDOSM district-level datasets.
                // Religious/program access can be added later as an internal YADIM service layer.
                $cni = round(
                    (0.40 * $povertyScore) +
                    (0.25 * $incomeGapScore) +
                    (0.20 * $youthRiskScore) +
                    (0.15 * $inequalityScore),
                    2
                );

                $districtId = DB::table('districts')->updateOrInsert(
                    ['state' => $row['state'], 'name' => $row['district']],
                    [
                        'code' => $this->districtCode($row['state'], $row['district']),
                        'slug' => Str::slug($row['state'].'-'.$row['district']),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $district = DB::table('districts')
                    ->where('state', $row['state'])
                    ->where('name', $row['district'])
                    ->first();

                DB::table('district_demographics')->updateOrInsert(
                    ['district_id' => $district->id, 'source_year' => (int) substr($row['population_date'], 0, 4)],
                    [
                        'population_total' => round($row['population_total']),
                        'youth_share' => round($row['youth_share'], 2),
                        'income_median' => round($row['income_median'], 2),
                        'income_mean' => round($row['income_mean'], 2),
                        'poverty_rate' => round($row['poverty_rate'], 2),
                        'gini' => round($row['gini'], 3),
                        'expenditure_mean' => round($row['expenditure_mean'], 2),
                        'population_source_date' => $row['population_date'],
                        'hies_source_date' => $row['hies_date'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                DB::table('community_need_scores')->updateOrInsert(
                    [
                        'district_id' => $district->id,
                        'year' => (int) substr($row['population_date'], 0, 4),
                    ],
                    [
                        'poverty_score' => round($povertyScore, 2),
                        'education_gap_score' => round($incomeGapScore, 2),
                        'youth_risk_score' => round($youthRiskScore, 2),
                        'religious_access_gap_score' => round($inequalityScore, 2),
                        'cni_score' => $cni,
                        'method_version' => 'opendosm_realdata_v1',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $inserted++;
            }
        });

        return [
            'districts_imported' => $inserted,
            'population_source_date' => $latestPopulationDate,
            'hies_source_date' => $latestHiesDate,
            'population_url' => self::OPENAPI_URL.'?id='.self::POPULATION_DATASET,
            'hies_url' => self::OPENAPI_URL.'?id='.self::HIES_DATASET,
        ];
    }

    private function downloadOpenApiDataset(string $dataset, int $limit): array
    {
        $response = Http::timeout(120)->retry(3, 1000)->get(self::OPENAPI_URL, [
            'id' => $dataset,
            'limit' => $limit,
        ]);
        $response->throw();

        $payload = $response->json();
        if (!is_array($payload)) {
            return [];
        }

        if (isset($payload['value']) && is_array($payload['value'])) {
            return $payload['value'];
        }

        return array_is_list($payload) ? $payload : [];
    }

    private function key(?string $state, ?string $district): string
    {
        $state = Str::lower(trim((string) $state));
        $district = Str::lower($this->canonicalDistrict($state, $district));


        return $state.'|'.$district;
    }

    private function canonicalDistrict(?string $state, ?string $district): string
    {
        $state = Str::lower(trim((string) $state));
        $district = trim((string) $district);

        if ($state === 'pahang' && in_array(Str::lower($district), ['cameron highland', 'cameron highlands'], true)) {
            return 'Cameron Highlands';
        }

        return $district;
    }

    private function districtCode(?string $state, ?string $district): string
    {
        return 'DOSM-'.Str::upper(Str::slug(trim((string) $state).'-'.trim((string) $district), '-'));
    }

    private function minMax(float $value, ?float $min, ?float $max): float
    {
        if ($min === null || $max === null || abs($max - $min) < 0.000001) {
            return 50.0;
        }
        return max(0, min(100, (($value - $min) / ($max - $min)) * 100));
    }

    private function inverseMinMax(float $value, ?float $min, ?float $max): float
    {
        return 100 - $this->minMax($value, $min, $max);
    }
}
