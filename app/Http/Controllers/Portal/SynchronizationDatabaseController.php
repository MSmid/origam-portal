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
use TCG\Voyager\Facades\Voyager;

class SynchronizationDatabaseController extends VoyagerDatabaseController
{

  /**
   * Method retrieves URL and check its availability by calling sync method.
   * Result is redirected to synchronization workflow view.
   *
   * @param \Illuminate\Http\Request $request
   * @param $id data source id
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function postCheck(Request $request, $id) {
    $url = DataSource::where('id', $id)->value('url');
    $data = $this->sync($url, $id);
    return $this->renderView('sync.sync', $id, $data);
  }

  /**
   * Method used for making GET calls on given URL.
   *
   * @param $url API endpoint for making the GET
   * @param $id data source id
   *
   * @return $output Array holding output data
   */
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

  /**
   * Method used for starting actual synchronization.
   *
   * @param $id data source id
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function syncStart($id) {

    event(new SyncStarted($id));

    $ds = DataSource::where('id', $id);
    $data = $this->sync($ds->value('url'), $id);
    //get model by entity_name
    $table = $ds->value('table_name');
    if (!isset($data['error'])) {
      //sync strategy:
      $this->databaseSyncDelete($table, $data['data']);
      $this->databaseSyncAdd($table, $ds->value('entity_name') ,$data['data']);
      $this->databaseSyncUpdate($table, $data['data']);
    } else {
      event(new SyncFailed($id, $data['error']));
    }
    event(new SyncSucceed($id));
    $data = ['result' => 'completed'];
    return $this->renderView('sync.sync', $id, $data);
  }

  /**
   * Method deleting rows which are not present in data retrieved from API
   *
   * @param $table name of the data table
   * @param $data
   *
   */
  public function databaseSyncDelete($table, $data) {
    //TODO
  }

  /**
   * Method adding rows to data table.
   * NOTE As far as prototype is concerned, syncing is simplified to truncating
   * data table and populating with data from Origam API.
   *
   * @param $table name of the data table
   * @param $entity name of the entity to parse data from
   * @param $data data retrieved from API
   *
   */
  public function databaseSyncAdd($table, $entity, $rawData) {
    $data = json_decode($rawData, true);
    DB::table($table)->truncate();
    //skip ROOT if present
    if(isset($data['ROOT'])) {
      $data = $data['ROOT'];
    }
    //skip entity
    if(isset($data[$entity])) {
      $data = $data[$entity];
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
   * Method for updating rows on matching UUID keys
   *
   * @param $table name of the data table
   * @param $data
   *
   */
  public function databaseSyncUpdate($table, $data) {
    //TODO
  }

  /**
   * Create database table by inicializing and displaying DB Manager.
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

  /**
   * Override method for inicializatin and population of DB manager user interface.
   *
   * @param $action
   * @param $table
   * @param $prefillData
   *
   * @return $db dataset required by DB Manager
   */
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

  /**
   * Helper method for storing processed data from Origam API
   *
   * @param $id data source id
   * @param $rawData data retrieved from Origam API
   *
   */
  public function storeSyncData($id, $rawData) {
    $data = json_decode($rawData, true);
    //ORIGAM
    if (isset($data['ROOT'])) {
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
  }

  /**
   * Helper method for storing entity name which represents
   * entity to be sync with.
   *
   * @param $id data source id
   * @param $rawData data retrieved from Origam API
   *
   */
  public function storeEntityName($id, $rawData) {
    $data = json_decode($rawData, true);
    $entityName = $this->getSyncDataEntityName($data);
    DataSource::where('id', $id)->update(['entity_name' => $entityName]);
  }

  /**
   * Helper method for displaying synchronization workflow view
   *
   * @param \Illuminate\Http\Request $request
   * @param $id data source id
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function showSync(Request $request, $id) {
    return $this->renderView('sync.sync', $id, null);
  }

  /**
   * Helper method returning synchronization view populated with data from
   * relevant data source table.
   *
   * @param $view name of the view
   * @param $id data source id
   * @param $data data to display
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function renderView($view, $id, $data) {
    $pageData = DataSource::query()->where('id', $id)->getQuery()->get()[0];
    return view($view, compact('pageData', 'data'));
  }

  public function getSyncDataEntityName($data) {
    foreach ($data as $entity => $properties) {
      return $entity;
    }
  }

}
