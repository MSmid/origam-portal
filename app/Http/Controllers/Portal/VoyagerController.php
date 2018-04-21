<?php

namespace App\Http\Controllers\Portal;

use App\DataSource;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Arrilot\Widgets\AbstractWidget;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerController as BaseVoyagerController;

class VoyagerController extends BaseVoyagerController
{

  public function index()
  {
      $this->generateDimmers();
      $this->getUserDataSources();
      return Voyager::view('index', [
        'ds' => $this->getActiveDataSources(),
        'user_ds' => $this->getUserDataSources()
      ]);
  }

  public function logout()
  {
      Auth::logout();

      return redirect(config('origam_portal.portal.domain') . '/login');
  }

  public function getUserDataSources() {
    if (Auth::check()) {
      $data = User::find(Auth::id())->dataSources()->get();
      foreach ($data as $key => $row) {
          $data[$key]->count = DB::table($row->table_name)->count();
          $data[$key]->iconClass = 'voyager-data';
          // dd($data[$key]);
      }
      // dd($data);
    }
    return $data;
     // $data = $ds->pivot->user_dashboard_settings;

  }

  public function getActiveDataSources() {
    $ds = DataSource::where('is_active', true)->get();
    return $ds->pluck('name', 'id');
  }

  public function generateDimmers() {
    if (Auth::check()) {
      // dd(Auth::id());
    }
  }
}
