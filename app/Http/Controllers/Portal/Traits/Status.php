<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait Status
{

    public function getStatus($id) {
      $status = DB::table('synchronizations')->where('data_source_id', $id)->orderBy('started_at', 'desc')->first();
      // dd($status);
      if($status) {
        return $status->value('status');
      }
      return 'none';
    }
}
