<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetBillingCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tps:getbilling';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Billing From BC';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::notice("Cron Get Billing Consol running at ". now());
        $response = \Http::get(config('app.url') . '/scheduler?jenis=getbilling');
    }
}
