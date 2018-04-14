<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Http\Request;
use TCG\Voyager\Http\Controllers\VoyagerBreadController as BaseVoyagerBreadController;

class SynchronizationBreadController extends BaseVoyagerBreadController
{

    public function browseSyncLog() {

    }

    public function getSlug(Request $request)
    {
        return 'data_sources';
    }
}
