<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('risk:scan-alerts --days=3')->dailyAt('07:00');
        // Keeps the current month's posture up to date; the value on the last
        // day of the month becomes that month's point on the dashboard trend.
        $schedule->command('risks:snapshot-scores')->dailyAt('23:50')->withoutOverlapping(30);
        $schedule->command('reports:dispatch-scheduled')->everyMinute();
        $schedule->command('threat-intel:refresh --force')
            ->everyFifteenMinutes()
            ->withoutOverlapping(30)
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
