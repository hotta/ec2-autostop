<?php

/*
|--------------------------------------------------------------------------
| Webルート
|--------------------------------------------------------------------------
|
| このファイルはアプリケーションで処理するすべてのルートを定義する場所です。
| クロージャやコントローラメソッドを使用し、レスポンスすべきURIを
| Laravelへ指示するだけです。素晴らしい物を作ってください！
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
