<?php

namespace App\Console;

use App\Console\Commands\ImportDosmRealData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ImportDosmRealData::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Optional: refresh DOSM data monthly.
        // $schedule->command('yadim:import-dosm')->monthly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
