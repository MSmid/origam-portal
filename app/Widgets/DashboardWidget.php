<?php

namespace App\Widgets;

use Arrilot\Widgets\AbstractWidget;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;

class DashboardWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [
      'count' => 0,
      'string' => '',
      'icon_class' => '',
      'url' => ''
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
     public function run()
     {
         // $count = Voyager::model('User')->count();
         // $string = trans_choice('voyager.dimmer.user', $count);
         $count = $this->config['count'];
         $string = $this->config['string'];
         $iconClass = $this->config['icon_class'];
         $url = str_replace('_', '-', config('origam_portal.portal.domain') . '/' . $this->config['url']);
         // $iconClass = 'voyager-data';

         return view('widgets.dashboard_widget', array_merge($this->config, [
             'icon'   => $iconClass,
             'title'  => "{$count} {$string}",
             'text'   => __('voyager.dimmer.user_text', ['count' => $count, 'string' => Str::lower($string)]),
             'button' => [
                 'text' => __('voyager.dimmer.user_link_text'),
                 'route' => route('voyager.users.index'),
                 'url' => $url
             ],
             'image' => voyager_asset('images/widget-backgrounds/01.jpg'),
         ]));
     }
}
