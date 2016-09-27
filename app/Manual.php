<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Manual extends Model
{
//  protected $table = 'manual';      //  実テーブル名
//  protected $primaryKey = 'id';     //  プライマリキー項目名
//  public $incrementing = true;      //  主キーは自動増分
//  public $timestamps = true;        //  タイムスタンプの自動更新
  /**
   * モデルの日付カラムの保存フォーマット
   *
   * @var string
   */
  protected $dateFormat = 'Y-m-d';    //  yyyy-mm-dd
//  protected $connection = 'pgsql';  //  接続名
}
