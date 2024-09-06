<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TarikResponCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tps:tarikrespon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tarik Respon Cron';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::notice("Cron Tarik Respon running at ". now());
        $response = \Http::get(config('app.url') . '/scheduler?jenis=respon');
        \Log::info('Cron Response: '.$response);
    }
}
