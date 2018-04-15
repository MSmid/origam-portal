<?php

namespace App\Http\Controllers\Portal;

use App\DataSource;
use App\Jobs\ProcessSynchronization;
use Illuminate\Http\Request;

class SynchronizationDatabaseController extends VoyagerDatabaseController
{

  public function sync(Request $request, $id) {
    $data = DataSource::query()->where('id', $id)->getQuery()->get()[0];
    $ds = DataSource::find($id);

    ProcessSynchronization::dispatch($ds)->delay(now()->addSeconds(10));

    return view('sync.sync', compact('data'));
  }

}
