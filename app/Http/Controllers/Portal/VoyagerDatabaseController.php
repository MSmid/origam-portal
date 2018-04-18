<?php

namespace App\Http\Controllers\Portal;

use App\DataSource;
use App\Notification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TCG\Voyager\Database\DatabaseUpdater;
use TCG\Voyager\Database\Schema\Column;
use TCG\Voyager\Database\Schema\Identifier;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Database\Schema\Table;
use TCG\Voyager\Database\Types\Type;
use TCG\Voyager\Events\BreadAdded;
use TCG\Voyager\Events\BreadDeleted;
use TCG\Voyager\Events\BreadUpdated;
use TCG\Voyager\Events\TableAdded;
use TCG\Voyager\Events\TableDeleted;
use TCG\Voyager\Events\TableUpdated;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Http\Controllers\VoyagerDatabaseController as BaseVoyagerDatabaseController;

class VoyagerDatabaseController extends BaseVoyagerDatabaseController
{

  public function store(Request $request)
  {
      Voyager::canOrFail('browse_database');

      try {
          $table = $request->table;
          //If it's datatable for notification work queue, there few more steps
          //to be done: prefix table name, create notification and add foreign keys
          if (isset($request->is_workqueue) && $request->is_workqueue == 'on') {
              //prefix
              $table = json_decode($request->table, true);
              $table['name'] = 'wq_' .$table['name'];
              //update DS entity_name
              $ds = DataSource::where('id', $request->ds_id);
              // $ds->update(['entity_name' => $table['name']]);
              //insert new notification
              $nid = DB::table('notifications')->insertGetId(
                ['name' => $ds->value('name'), 'data_source_id' => $request->ds_id, 'slug_datatable_name' => $table['name']]
              );
              //Add foreing keys on notificaiton id and user id, is_read flag, subject
              array_push(
                $table['columns'], [
                  'name' => 'notification_id',
                  'oldname' => '',
                  'type' => [
                    'name' => 'integer',
                    'category' => 'Numbers',
                    'default' => [
                      'type' => 'number',
                      'step' => 'any']
                  ],
                  'length' => null,
                  'fixed' => false,
                  'unsigned' => true,
                  'autoincrement' => false,
                  'notnull' => true,
                  'default' => null
                ]);
              array_push($table['indexes'], ['columns' => ['notification_id'], 'type' => 'INDEX', 'name' => '', 'table' => 'Notifications']);
              array_push(
                $table['columns'], [
                  'name' => 'is_read',
                  'oldname' => '',
                  'type' => [
                    'name' => 'tinyint',
                    'category' => 'Numbers',
                    'default' => [
                      'type' => 'number',
                      'step' => 'any']
                  ],
                  'length' => null,
                  'fixed' => false,
                  'unsigned' => false,
                  'autoincrement' => false,
                  'notnull' => false,
                  'default' => 0
                ]);
              array_push(
                $table['columns'], [
                  'name' => 'Subject',
                  'oldname' => '',
                  'type' => [
                    'name' => 'text',
                    'category' => 'Strings',
                    'notSupportIndex' => true,
                    'default' => [
                      'disabled' => true]
                  ],
                  'length' => null,
                  'fixed' => false,
                  'unsigned' => false,
                  'autoincrement' => false,
                  'notnull' => false,
                  'default' => null
                ]);
          }
          // dd(json_decode($table, true));
          Type::registerCustomPlatformTypes();

          $table = Table::make($table);
          SchemaManager::createTable($table);

          if (isset($request->create_model) && $request->create_model == 'on') {
              $modelNamespace = config('voyager.models.namespace', app()->getNamespace());
              $params = [
                  'name' => $modelNamespace.Str::studly(Str::singular($table->name)),
              ];

              // if (in_array('deleted_at', $request->input('field.*'))) {
              //     $params['--softdelete'] = true;
              // }

              if (isset($request->create_migration) && $request->create_migration == 'on') {
                  $params['--migration'] = true;
              }

              Artisan::call('voyager:make:model', $params);
          } elseif (isset($request->create_migration) && $request->create_migration == 'on') {
              Artisan::call('make:migration', [
                  'name'    => 'create_'.$table->name.'_table',
                  '--table' => $table->name,
              ]);
          }

          event(new TableAdded($table));

          if ($request->input('ds_id')) {
            return $this->updateDataSourceAndRedirect($request->input('ds_id'), $table->name);
          }

          return redirect()
             ->route('voyager.database.index')
             ->with($this->alertSuccess(__('voyager.database.success_create_table', ['table' => $table->name])));
      } catch (Exception $e) {
          return back()->with($this->alertException($e))->withInput();
      }
  }

  public function updateDataSourceAndRedirect($id, $tableName) {
    DataSource::where('id', $id)->update(['is_synced' => true]);
    return redirect()
       ->route('voyager.data_sources.index')
       ->with($this->alertSuccess(__('origam_portal.database.success_create_sync', ['table' => $tableName, 'dsname' => DataSource::where('id', $id)->value('name')])));
  }

}
