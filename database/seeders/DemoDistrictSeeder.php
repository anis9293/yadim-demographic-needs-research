<?php

namespace Database\Seeders;

use App\Models\DemographicStat;
use App\Models\District;
use App\Services\CniScoringService;
use Illuminate\Database\Seeder;

class DemoDistrictSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code'=>'MY-SEL-HLU', 'name'=>'Hulu Langat', 'state'=>'Selangor', 'lat'=>3.0679, 'lng'=>101.7610, 'population'=>1400000, 'poverty'=>4.2, 'income'=>8500, 'edu_gap'=>18, 'youth'=>7.8, 'religious_gap'=>22],
            ['code'=>'MY-KEL-GMS', 'name'=>'Gua Musang', 'state'=>'Kelantan', 'lat'=>4.8844, 'lng'=>101.9686, 'population'=>105000, 'poverty'=>12.5, 'income'=>4100, 'edu_gap'=>34, 'youth'=>12.2, 'religious_gap'=>36],
            ['code'=>'MY-SAB-BFT', 'name'=>'Beaufort', 'state'=>'Sabah', 'lat'=>5.3473, 'lng'=>115.7455, 'population'=>72000, 'poverty'=>15.0, 'income'=>3600, 'edu_gap'=>42, 'youth'=>13.5, 'religious_gap'=>40],
            ['code'=>'MY-PRK-BGN', 'name'=>'Bagan Datuk', 'state'=>'Perak', 'lat'=>3.9950, 'lng'=>100.7860, 'population'=>70000, 'poverty'=>8.6, 'income'=>4700, 'edu_gap'=>30, 'youth'=>9.4, 'religious_gap'=>26],
            ['code'=>'MY-JHR-JB', 'name'=>'Johor Bahru', 'state'=>'Johor', 'lat'=>1.4927, 'lng'=>103.7414, 'population'=>1700000, 'poverty'=>2.8, 'income'=>9200, 'edu_gap'=>14, 'youth'=>6.0, 'religious_gap'=>18],
        ];

        $scorer = app(CniScoringService::class);

        foreach ($rows as $row) {
            $district = District::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'state' => $row['state'],
                    'latitude' => $row['lat'],
                    'longitude' => $row['lng'],
                ]
            );

            $stat = DemographicStat::updateOrCreate(
                ['district_id' => $district->id, 'year' => 2024],
                [
                    'population' => $row['population'],
                    'poverty_rate' => $row['poverty'],
                    'median_income' => $row['income'],
                    'education_gap_rate' => $row['edu_gap'],
                    'youth_unemployment_rate' => $row['youth'],
                    'religious_access_gap_rate' => $row['religious_gap'],
                    'source_name' => 'Demo data - replace with DOSM/OpenDOSM imports',
                    'source_url' => 'https://open.dosm.gov.my/data-catalogue',
                ]
            );

            $scorer->calculateAndSave($stat);
        }
    }
}
