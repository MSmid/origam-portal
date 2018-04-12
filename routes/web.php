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


Route::group(['prefix' => 'portal'], function () {
    Voyager::routes();
    Route::get('login', ['uses' => 'Portal\LoginController@login', 'as' => 'login']);
    Route::post('login', ['uses' => 'Portal\LoginController@postLogin', 'as' => 'postLogin']);
    Route::get('test', ['uses' => 'Portal\MovieController@index']);
});
