<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Barkir;

class GetResponCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tps:get30respon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get 30 Respon BC';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $barkir = new Barkir;
        $npwp = ['930976485402000', '862102258402000', '018020776017000'];

        foreach($npwp as $n)
        {
          $barkir->getrespon($n);
        }
    }
}
