<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;

class PortalMenuItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (file_exists(base_path('routes/web.php'))) {
            require base_path('routes/web.php');

            $menu = Menu::where('name', 'admin')->firstOrFail();

            //Sync
            $syncMenuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => __('origam_portal.seeders.menu_items.sync'),
                'url'     => '',
            ]);
            if (!$syncMenuItem->exists) {
                $syncMenuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'voyager-data',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 10,
                ])->save();
            }

            //Sync -> Origam
            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Origam',
                'url'     => '',
                'route'      => 'portal.synchronization.origam.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'voyager-data',
                    'color'      => null,
                    'parent_id'  => $syncMenuItem->id,
                    'order'      => 1,
                ])->save();
            }

            //Sync -> Webservices
            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => __('origam_portal.seeders.menu_items.web_services'),
                'url'     => '',
                'route'      => 'portal.synchronization.services.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'voyager-data',
                    'color'      => null,
                    'parent_id'  => $syncMenuItem->id,
                    'order'      => 2,
                ])->save();
            }

            //Sync -> Data Sources
            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => __('origam_portal.seeders.menu_items.data_sources'),
                'url'     => '',
                'route'      => 'voyager.data_sources.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'voyager-data',
                    'color'      => null,
                    'parent_id'  => $syncMenuItem->id,
                    'order'      => 3,
                ])->save();
            }

            //Sync -> Scheduler
            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => __('origam_portal.seeders.menu_items.scheduler'),
                'url'     => '',
                'route'      => 'voyager.scheduler.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'voyager-calendar',
                    'color'      => null,
                    'parent_id'  => $syncMenuItem->id,
                    'order'      => 10,
                ])->save();
            }

            //Notifications
            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => __('origam_portal.seeders.menu_items.notifications'),
                'url'     => '',
                'route'      => 'voyager.notifications.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'voyager-mail',
                    'color'      => null,
                    'parent_id'  => '',
                    'order'      => 6,
                ])->save();
            }
        }
    }
}
