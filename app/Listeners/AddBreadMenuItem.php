<?php

namespace App\Listeners;

use App\Events\PortalBreadAdded;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;

class AddBreadMenuItem
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
     * Create a MenuItem for a given BREAD.
     *
     * @param PortalBreadAdded $event
     *
     * @return void
     */
    public function handle(PortalBreadAdded $bread)
    {
        if (config('voyager.add_bread_menu_item') && file_exists(base_path('routes/web.php'))) {
            require base_path('routes/web.php');

            $menu = Menu::where('name', 'admin')->firstOrFail();

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => $bread->dataType->display_name_plural,
                // 'url'     => '/'.config('voyager.prefix', 'admin').'/'.$bread->dataType->slug,
                'url' => '',
                'route' => 'voyager.' . $bread->dataType->slug . '.index'
            ]);

            $order = Voyager::model('MenuItem')->highestOrderMenuItem();

            if (!$menuItem->exists) {

                $parentId = MenuItem::where('title', '=', 'Data')->value('id');

                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => $bread->dataType->icon,
                    'color'      => null,
                    'parent_id'  => $parentId,
                    'order'      => $order,
                ])->save();
            }
        }
    }
}
