<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Traits\Seedable;

class MainDatabaseSeeder extends Seeder
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
        $this->seed('VoyagerDatabaseSeeder');

        $this->seed('PortalUsersTableSeeder');

        $this->seed('PortalDatabaseSeeder');

        //Run last to seed permissions
        $this->seed('PermissionRoleTableSeeder');
    }
}
