<?php

namespace App\Listeners;

use App\Events\ScanHouse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendHouseNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ScanHouse  $event
     * @return void
     */
    public function handle(ScanHouse $event)
    {
        //
    }
}
