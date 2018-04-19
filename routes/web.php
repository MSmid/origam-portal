<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => config('origam_portal.portal.domain')], function () {
    Voyager::routes();

    Route::get('login', ['uses' => 'Portal\LoginController@login', 'as' => 'login']);
    Route::post('login', ['uses' => 'Portal\LoginController@postLogin', 'as' => 'postLogin']);
    // Route::post('logout', ['uses' => 'Portal\VoyagerController@logout',  'as' => 'logout']);

    Route::group(['as' => 'portal.'], function () {
      //Synced Tables
      // try {
      //     foreach (DataType::all() as $dataType) {
      //         $breadController = $dataType->controller
      //                          ? $dataType->controller
      //                          : $namespacePrefix.'VoyagerBreadController';
      //
      //         Route::resource($dataType->slug, $breadController);
      //     }
      // } catch (\InvalidArgumentException $e) {
      //     throw new \InvalidArgumentException("Custom routes hasn't been configured because: ".$e->getMessage(), 1);
      // } catch (\Exception $e) {
      //     // do nothing, might just be because table not yet migrated.
      // }
      // Synchronization
      Route::group([
        'as' => 'synchronization.',
        'prefix' => 'synchronization'
      ], function () {
        Route::get('/', function () {
          return redirect(config('origam_portal.portal.domain') . '/data_sources');
        });
        Route::get('origam', ['uses' => 'Portal\OrigamSyncController@index', 'as' => 'origam.index']);
        Route::get('services', ['uses' => 'Portal\WebServicesSyncController@index', 'as' => 'services.index']);
        Route::get('{id}', ['uses' => 'Portal\SynchronizationBreadController@show', 'as' => 'show']);
        Route::get('{id}/sync', ['uses' => 'Portal\SynchronizationDatabaseController@showSync', 'as' => 'sync']);
        Route::post('{id}/check', ['uses' => 'Portal\SynchronizationDatabaseController@postCheck', 'as' => 'check']);
        Route::get('{id}/create', ['uses' => 'Portal\SynchronizationDatabaseController@createSync', 'as' => 'create']);
        Route::post('{id}/sync', ['uses' => 'Portal\SynchronizationDatabaseController@syncStart', 'as' => 'syncStart']);
      });
      // Notifications
      Route::group([
        'as' => 'notifications.',
        'prefix' => 'notifications'
      ], function(){
        Route::get('{slug}', ['uses' => 'Portal\NotificationController@showNotifications', 'as' => 'show']);
        Route::get('{slug}/{uuid}', ['uses' => 'Portal\NotificationController@showNotificationMessage', 'as' => 'showMsg']);
        Route::get('{slug}/{uuid}/mark', ['uses' => 'Portal\NotificationController@markAsRead', 'as' => 'mark']);
      });
      //Log
      Route::get('log', ['uses' => 'Portal\PortalLogController@index', 'as' => 'index']);
    });

});
