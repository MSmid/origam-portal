<?php

namespace App\Listeners;

use App\Events\SyncFailed;
use App\Synchronization;
use App\DataSource;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncUpdateFail
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
    public function handle(SyncFailed $event)
    {
      //update DataSource last_sync_at
      $current_time = Carbon::now()->toDateTimeString();
      $ds = DataSource::where('id', $event->dataSourceId);
      $ds->update(['last_sync_at' => $current_time]);
      //update Synchronization status and finished_at
      $sync = Synchronization::where('data_source_id', $event->dataSourceId)
        ->orderBy('started_at', 'desc')->limit(1);
      if ($sync) {
        $sync->update([
          'status' => 'failed',
          'finished_at' => $current_time,
          'message' => $event->errMsg
        ]);
      }
    }
}
