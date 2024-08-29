<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TpsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tps:permit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Scheduler for SPPB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::notice("Cron PLP Job running at ". now());
        $response = \Http::get(config('app.url') . '/scheduler?jenis=plp');
        \Log::info('Cron Response: '.$response);

        \Log::notice("Cron PIB Job running at ". now());
        $response = \Http::get(config('app.url') . '/scheduler?jenis=importpermit');
        \Log::info('Cron Response: '.$response);

        \Log::notice("Cron BC23 Job running at ". now());
        $response = \Http::get(config('app.url') . '/scheduler?jenis=bc23permit');
        \Log::info('Cron Response: '.$response);
    }
}
