<?php

namespace App\Http\Controllers\Portal;

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
  public function index()
  {
      Voyager::canOrFail('browse_database');

      $dataTypes = Voyager::model('DataType')->select('id', 'name', 'slug')->get()->keyBy('name')->toArray();

      $tables = array_map(function ($table) use ($dataTypes) {
          $table = [
              'name'       => $table,
              'slug'       => isset($dataTypes[$table]['slug']) ? $dataTypes[$table]['slug'] : null,
              'dataTypeId' => isset($dataTypes[$table]['id']) ? $dataTypes[$table]['id'] : null,
          ];

          return (object) $table;
      }, SchemaManager::listTableNames());

      return Voyager::view('database.index')->with(compact('dataTypes', 'tables'));
  }

  /**
   * Create database table.
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function create()
  {
      Voyager::canOrFail('browse_database');

      $db = $this->prepareDbManager('create');

      return Voyager::view('database.edit-add', compact('db'));
  }
}
