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

            $OrigamSyncMenuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Origam',
                'url'     => '',
                'route'      => 'portal.synchronization.origam.index',
            ]);
            if (!$OrigamSyncMenuItem->exists) {
                $OrigamSyncMenuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'voyager-data',
                    'color'      => null,
                    'parent_id'  => $syncMenuItem->id,
                    'order'      => 1,
                ])->save();
            }

            $OrigamSyncMenuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => __('origam_portal.seeders.menu_items.web_services'),
                'url'     => '',
                'route'      => 'portal.synchronization.services.index',
            ]);
            if (!$OrigamSyncMenuItem->exists) {
                $OrigamSyncMenuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'voyager-data',
                    'color'      => null,
                    'parent_id'  => $syncMenuItem->id,
                    'order'      => 2,
                ])->save();
            }
        }
    }
}
