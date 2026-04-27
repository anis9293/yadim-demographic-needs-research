<?php

namespace App\Console\Commands;

use App\Services\DosmRealDataImportService;
use Illuminate\Console\Command;

class ImportDosmRealData extends Command
{
    protected $signature = 'yadim:import-dosm {--state= : Optional state filter, e.g. Selangor} {--limit= : Optional district limit for testing}';
    protected $description = 'Import real OpenDOSM district population and HIES income/poverty data, then calculate CNI.';

    public function handle(DosmRealDataImportService $service): int
    {
        $this->info('Importing real OpenDOSM data...');

        $result = $service->import(
            state: $this->option('state'),
            limit: $this->option('limit') ? (int) $this->option('limit') : null,
            logger: fn (string $message) => $this->line($message)
        );

        $this->info('Done.');
        $this->table(['Metric', 'Value'], collect($result)->map(fn($v, $k) => [$k, $v])->all());

        return self::SUCCESS;
    }
}
