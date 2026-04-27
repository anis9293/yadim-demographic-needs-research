<?php

namespace App\Console\Commands;

use App\Models\DemographicStat;
use App\Services\CniScoringService;
use Illuminate\Console\Command;

class RecalculateCniScores extends Command
{
    protected $signature = 'yadim:recalculate-cni {--year=}';
    protected $description = 'Recalculate Community Need Index scores from demographic stats.';

    public function handle(CniScoringService $scoringService): int
    {
        $query = DemographicStat::query();

        if ($this->option('year')) {
            $query->where('year', (int) $this->option('year'));
        }

        $count = 0;
        $query->chunkById(100, function ($stats) use ($scoringService, &$count) {
            foreach ($stats as $stat) {
                $scoringService->calculateAndSave($stat);
                $count++;
            }
        });

        $this->info("Recalculated {$count} CNI records.");
        return self::SUCCESS;
    }
}
