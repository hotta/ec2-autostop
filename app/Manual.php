<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Manual extends Model
{
  /**
   * モデルの日付カラムの保存フォーマット
   *
   * @var string
   */
  protected $dateFormat = 'Y-m-d';  //  yyyy-mm-dd
}
