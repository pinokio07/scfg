<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('tps:getbilling')
                ->hourly()
                ->between('00:00', '05:00')
                ->runInBackground();        
        $schedule->command('app:exrate')
                ->hourly()
                ->between('6:00', '11:00')
                ->runInBackground();
        $schedule->command('tps:exratetax')
                ->hourly()
                ->between('6:00', '9:00')
                ->runInBackground();  
        $schedule->command('tps:tarikrespon')
                ->everyThreeMinutes()
                ->withoutOverlapping()
                ->runInBackground();
        $schedule->command('tps:permit')
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground();
        $schedule->command('tps:gateinout')
                ->everyThirtySeconds()
                ->withoutOverlapping()
                ->runInBackground();
        $schedule->command('tps:get30respon')
                ->everySecond()
                ->withoutOverlapping()
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
