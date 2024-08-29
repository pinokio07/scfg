<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GateInOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tps:gateinout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Gate In/Out';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::notice("Cron Gate In Job running at ". now());
        $response = \Http::get(config('app.url') . '/scheduler?jenis=gatein');
        \Log::info('Cron Response: '.$response);

        \Log::notice("Cron Gate Out Job running at ". now());
        $response = \Http::get(config('app.url') . '/scheduler?jenis=gateout');
        \Log::info('Cron Response: '.$response);
    }
}
