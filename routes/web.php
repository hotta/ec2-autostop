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

Route::get('/', [
  'as'    =>  'manual.index',             //  テストで使う
  'uses'  =>  'ManualsController@index'
]);
//  Route::get('/', function () { return view('welcome'); }); //  後勝ち
Route::get('/public/index.php', 'ManualsController@index');
Route::post('/manual/start/{instance_id}/{nickname}',
  'ManualsController@start');
Route::post('/manual/stop/{instance_id}/{nickname}',
  'ManualsController@stop');
Route::post('/manual/to_manual/{instance_id}/{nickname}',
  'ManualsController@to_manual');
