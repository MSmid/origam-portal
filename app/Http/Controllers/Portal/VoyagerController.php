<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerController as BaseVoyagerController;

class VoyagerController extends BaseVoyagerController
{

  public function index()
  {
      return Voyager::view('index', ['test' => 'test']);
  }

  public function logout()
  {
      Auth::logout();

      return redirect(config('origam_portal.portal.domain') . '/login');
  }
}
