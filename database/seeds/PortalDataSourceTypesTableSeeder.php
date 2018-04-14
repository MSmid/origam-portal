<?php

use Illuminate\Database\Seeder;
use App\DataSourceType;

class PortalDataSourceTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $type = DataSourceType::firstOrNew([
          'name' => 'Origam',
      ]);
      if (!$type->exists) {
          $type->fill([
              'name' => 'Origam',
          ])->save();
      }

      $type = DataSourceType::firstOrNew([
          'name' => 'Web Service',
      ]);
      if (!$type->exists) {
          $type->fill([
              'name' => 'Web Service',
          ])->save();
      }
    }
}
