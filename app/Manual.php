<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Manual extends Model
{
//  protected $table = 'manual';      //  実テーブル名
//  protected $primaryKey = 'id';     //  プライマリキー項目名
//  public $incrementing = true;      //  主キーは自動増分
//  public $timestamps = true;        //  タイムスタンプの自動更新
//  protected $dateFormat = 'Y-m-d';  //  モデルの日付カラムの保存フォーマット
//  protected $connection = 'pgsql';  //  接続名
  protected $fillable = [             //  フォームから設定可能な項目
    't_date', 'instance_id', 'nickname'
  ];
}
