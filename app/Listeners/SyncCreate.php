<?php

namespace App\Listeners;

use App\Events\SyncStarted;
use App\Synchronization;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncCreate
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
     * @param  object  $event
     * @return void
     */
    public function handle(SyncStarted $event)
    {
        $sync = new Synchronization;
        $sync->fill([
            'data_source_id' => $event->dataSourceId,
            'status' => 'in-progress'
          ])->save();
    }
}
