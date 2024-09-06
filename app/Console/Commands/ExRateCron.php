<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExRateCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:exrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download Exchange Rate from BI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::notice("Cron Exchange Rate Job running at ". now().'; Url: '.env('APP_URL') . '/syncrate');
        $response = \Http::get(config('app.url') . '/syncrate');
        \Log::info('Cron Response: '.$response);
    }
}
