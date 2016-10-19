<?php

/*
|--------------------------------------------------------------------------
| アプリケーションのルート
|--------------------------------------------------------------------------
|
| ここでアプリケーションのルートを全て登録することが可能です。
| 簡単です。ただ、Laravelへ対応するURIと、そのURIがリクエスト
| されたときに呼び出されるコントローラを指定してください。
|
*/

Route::get('/', 'ManualsController@index');
//  Route::get('/', function () { return view('welcome'); }); //  後勝ち
Route::get('/public/index.php', 'ManualsController@index');
Route::post('/manual/start/{instance_id}/{nickname}',
  'ManualsController@start');
Route::post('/manual/stop/{instance_id}/{nickname}', 
  'ManualsController@stop');
Route::post('/manual/to_manual/{instance_id}/{nickname}', 
  'ManualsController@to_manual');
