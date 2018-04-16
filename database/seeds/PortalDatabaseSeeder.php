<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Traits\Seedable;

class PortalDatabaseSeeder extends Seeder
{
    use Seedable;

    protected $seedersPath = __DIR__.'/';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Data Sources
        $this->seed('PortalDataSourceTypesTableSeeder');
        $this->seed('PortalDataSourcesTableSeeder');
        $this->seed('PortalDataTypesTableSeeder');
        $this->seed('PortalDataRowsTableSeeder');

        //Menu Items
        $this->seed('PortalMenuItemsTableSeeder');

        //Permissions
        $this->seed('PortalPermissionsTableSeeder');

        //Settings
        $this->seed('PortalSettingsTableSeeder');
    }
}
