<?php

namespace App\Http\Controllers\Portal;

use App\DataSource;
use App\Events\SyncStarted;
use App\Events\SyncSucceed;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

  public function postCheck(Request $request, $id) {
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
    $this->storeEntityName($id, $data);
    DataSource::where('id', $id)->update(['sync_data' => $data]);
  }

  public function storeEntityName($id, $rawData) {
    $data = json_decode($rawData, true);
    $entityName = $this->getSyncDataEntityName($data);
    DataSource::where('id', $id)->update(['entity_name' => $entityName]);
  }

  public function syncStart($id) {

    event(new SyncStarted($id));
    
    $ds = DataSource::where('id', $id);
    $data = $this->sync($ds->value('url'), $id);
    //get model by entity_name
    $table = $ds->value('entity_name');
    if (!isset($data['error'])) {
      //sync strategy:
      //1) add only new rows by uuid
      $this->databaseSyncAdd($table, $data['data']);
      //2) delete rows not present in $data by uuid
      //$this->databaseSyncDelete($table, $data['data']);
      //3) update the values of rows with same uuid
      //$this->databaseSyncUpdate($table, $data['data']);
    } else {
      event(new SyncFailed($id, $data['error']));
    }
    event(new SyncSucceed($id));
    $data = ['result' => 'completed'];
    return $this->renderView('sync.sync', $id, $data);
  }

  public function databaseSyncAdd($table, $rawData) {
    $data = json_decode($rawData, true);
    DB::table($table)->truncate();
    //skip ROOT if present
    if(isset($data['ROOT'])) {
      $data = $data['ROOT'];
    }
    //skip entity
    if(isset($data[$table])) {
      $data = $data[$table];
    }
    //loop over dataset, rename id to uuid
    foreach ($data as $row) {
      if (isset($row['Id'])) {
        $row['uuid'] = $row['Id'];
        unset($row['Id']);
      }
      DB::table($table)->insert(
        $row
      );
    }
  }

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
      $db->ds_id = $id;

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
          // Add columns based on sync except Id
          foreach($prefill as $row) {
            foreach($row as $column => $value) {
              if ($column != 'Id') {
                $db->table->addColumn($column, 'text', [
                    'unsigned'      => false,
                    'notnull'       => false,
                    'autoincrement' => false,
                ]);
              }
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
