<?php

namespace App\Http\Controllers\Portal;

use App\DataSource;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
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

class SynchronizationDatabaseController extends VoyagerDatabaseController
{

  public function showSync(Request $request, $id) {
    return $this->renderView('sync.sync', $id, null);
  }

  public function postSync(Request $request, $id) {
    $url = DataSource::where('id', $id)->value('url');
    $data = $this->sync($url, $id);
    return $this->renderView('sync.sync', $id, $data);
  }

  public function sync($url, $id) {
    $client = new Client();
    $path = env('ORIGAM_BASE_URL') . '/' . $url;
    $promise = $client->getAsync($path)->then(
        function ($res) use ($id) {
          $rawData = $res->getBody()->getContents();
          $this->storeSyncData($id, $rawData);
          $this->storeEntityName($id, $rawData);
          return [
            'data' => $rawData
          ];
        },
        function($error) {
          return [
            'error' => $error->getMessage()
          ];
        }
      );
    $output = $promise->wait();
    return $output;
  }

  public function renderView($view, $id, $data) {
    $pageData = DataSource::query()->where('id', $id)->getQuery()->get()[0];
    return view($view, compact('pageData', 'data'));
  }

  public function storeSyncData($id, $rawData) {
    $data = json_decode($rawData, true);
    $data = $data['ROOT'];
    foreach($data as $row => $value) {
      if (count($value) > 0) {
        $data[$row] = $value[0];
        break;
      }
      $data = $data;
      break;
    }
    $data = json_encode($data);
    DataSource::where('id', $id)->update(['sync_data' => $data]);
  }

  public function storeEntityName($id, $rawData) {
    $data = json_decode($rawData, true);
    $entityName = $this->getSyncDataEntityName($data);
    DataSource::where('id', $id)->update(['entity_name' => $entityName]);
  }

  public function syncStart($id) {
    $ds = DataSource::where('id', $id);

    $data = $this->sync($ds->value('url'), $id);
    //get model by entity_name
    //sync strategy:
    //1) add only new rows by uuid
    //2) delete rows not present in $data by uuid
    //3) update the same uuid
    // dd($data);
    $data = ['result' => 'completed'];
    return $this->renderView('sync.sync', $id, $data);
  }

  // public function index()
  // {
  //     Voyager::canOrFail('browse_database');
  //
  //     $dataTypes = Voyager::model('DataType')->select('id', 'name', 'slug')->get()->keyBy('name')->toArray();
  //
  //     $tables = array_map(function ($table) use ($dataTypes) {
  //         $table = [
  //             'name'       => $table,
  //             'slug'       => isset($dataTypes[$table]['slug']) ? $dataTypes[$table]['slug'] : null,
  //             'dataTypeId' => isset($dataTypes[$table]['id']) ? $dataTypes[$table]['id'] : null,
  //         ];
  //
  //         return (object) $table;
  //     }, SchemaManager::listTableNames());
  //
  //     return Voyager::view('database.index')->with(compact('dataTypes', 'tables'));
  // }

  /**
   * Create database table.
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function createSync($id)
  {
      Voyager::canOrFail('browse_database');
      $prefillData = DataSource::where('id', $id)->value('sync_data');
      $db = $this->prepareDbManager('create', null, $prefillData);

      return Voyager::view('database.edit-add', compact('db'));
  }

  protected function prepareDbManager($action, $table = '', $prefillData = '')
  {
      $prefill = json_decode($prefillData, true);
      $db = new \stdClass();

      // Need to get the types first to register custom types
      $db->types = Type::getPlatformTypes();

      if ($action == 'update') {
          $db->table = SchemaManager::listTableDetails($table);
          $db->formAction = route('voyager.database.update', $table);
      } else {
          $tableName = $this->getSyncDataEntityName($prefill);
          $db->table = new Table($tableName);
          // Add prefilled columns
          $db->table->addColumn('id', 'integer', [
              'unsigned'      => true,
              'notnull'       => true,
              'autoincrement' => true,
          ]);
          $db->table->addColumn('uuid', 'text', [
              'unsigned'      => false,
              'notnull'       => false,
              'autoincrement' => false,
          ]);
          // Add columns based on sync
          foreach($prefill as $row) {
            foreach($row as $column => $value) {
              $db->table->addColumn($column, 'text', [
                  'unsigned'      => false,
                  'notnull'       => false,
                  'autoincrement' => false,
              ]);
            }
          }

          $db->table->setPrimaryKey(['id'], 'primary');

          $db->formAction = route('voyager.database.store');
      }

      $oldTable = old('table');
      $db->oldTable = $oldTable ? $oldTable : json_encode(null);
      $db->action = $action;
      $db->identifierRegex = Identifier::REGEX;
      $db->platform = SchemaManager::getDatabasePlatform()->getName();

      return $db;
  }

  public function getSyncDataEntityName($data) {
    foreach ($data as $entity => $properties) {
      return $entity;
    }
  }

}
