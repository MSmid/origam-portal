<?php

namespace App\Http\Controllers\Portal;

use App\DataSource;
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
          Type::registerCustomPlatformTypes();

          $table = Table::make($request->table);
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
       ->route('portal.data_sources.index')
       ->with($this->alertSuccess(__('origam_portal.database.success_create_sync', ['table' => $tableName, 'dsname' => DataSource::where('id', $id)->value('name')])));
  }

}
