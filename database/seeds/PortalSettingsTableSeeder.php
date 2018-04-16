<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Setting;

class PortalSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stg = Setting::where('key', 'admin.title')->firstOrFail();
        if ($stg) {
            $stg->fill([
                'value'     => 'Origam Portal'
            ])->save();
        }
    }
}
