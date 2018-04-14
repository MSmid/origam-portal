<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Permission;

class PortalPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
     public function run()
     {
         $keys = [
             'browse_admin',
             'browse_database',
             'browse_media',
             'browse_compass',
         ];

         foreach ($keys as $key) {
             Permission::firstOrCreate([
                 'key'        => $key,
                 'table_name' => null,
             ]);
         }

         Permission::generateFor('data_sources');
     }

}
