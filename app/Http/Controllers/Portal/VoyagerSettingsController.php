<?php

namespace App\Http\Controllers\Portal;

use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerSettingsController as BaseVoyagerSettingsController;

class VoyagerSettingsController extends BaseVoyagerSettingsController
{
  public function index()
  {
      // Check permission
      $this->authorize('browse', Voyager::model('Setting'));

      $data = Voyager::model('Setting')->orderBy('order', 'ASC')->get();

      $settings = [];
      $settings[__('voyager.settings.group_general')] = [];
      foreach ($data as $d) {
          if ($d->group == '' || $d->group == __('voyager.settings.group_general')) {
              $settings[__('voyager.settings.group_general')][] = $d;
          } else {
              $settings[$d->group][] = $d;
          }
      }
      if (count($settings[__('voyager.settings.group_general')]) == 0) {
          unset($settings[__('voyager.settings.group_general')]);
      }

      $groups_data = Voyager::model('Setting')->select('group')->distinct()->get();
      $groups = [];
      foreach ($groups_data as $group) {
          if ($group->group != '') {
              $groups[] = $group->group;
          }
      }

      return Voyager::view('settings.index', compact('settings', 'groups'));
  }
}
