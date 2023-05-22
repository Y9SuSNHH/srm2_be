<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use App\Console\Commands\LearningProcessCommand;
use App\Console\Commands\TransactionCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        LearningProcessCommand::class,
        TransactionCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('job:LearningProcess')->daily()->withoutOverlapping();
        $schedule->command('job:CreateStudentCredit')->dailyAt('06:00')->withoutOverlapping();
    }
}
