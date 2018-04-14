<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\DataType;

class PortalDataTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
     public function run()
     {
         $dataType = $this->dataType('slug', 'data_sources');
         if (!$dataType->exists) {
             $dataType->fill([
                 'name'                  => 'data_sources',
                 'display_name_singular' => __('origam_portal.seeders.data_types.data_source.singular'),
                 'display_name_plural'   => __('origam_portal.seeders.data_types.data_source.plural'),
                 'icon'                  => 'voyager-data',
                 'model_name'            => 'App\DataSource',
                 'policy_name'           => 'TCG\\Voyager\\Policies\\PostPolicy',
                 'controller'            => '',
                 'generate_permissions'  => 1,
                 'description'           => '',
             ])->save();
         }
     }

     /**
      * [dataType description].
      *
      * @param [type] $field [description]
      * @param [type] $for   [description]
      *
      * @return [type] [description]
      */
     protected function dataType($field, $for)
     {
         return DataType::firstOrNew([$field => $for]);
     }
}
