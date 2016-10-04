<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support;

class FakeEc2 extends Model
{
  protected $table = 'fake_ec2';          //  実テーブル名
  protected $primaryKey = 'instance_id';  //  プライマリキー項目名
  public $incrementing = false;           //  主キーは自動増分?

  /**
   * インスタンスの起動
   *
   * @return void
   */
  public function start($instance_id)
  {
    \Log::info(sprintf("%s::%s(%s) called.",
      __CLASS__, __METHOD__, $instance_id));
    $ec2 = $this->find($instance_id);
  } //  FakeEc2 :: start()

  /**
   * インスタンスの停止
   *
   * @return void
   */
  public function stop($instance_id)
  {
    \Log::info(sprintf("%s::%s(%s) called.",
      __CLASS__, __METHOD__, $instance_id));
  } //  FakeEc2 :: stop()

  /**
   * インスタンスの再起動
   *
   * @return void
   */
  public function reboot($instance_id)
  {
    \Log::info(sprintf("%s::%s(%s) called.",
      __CLASS__, __METHOD__, $instance_id));
  } //  FakeEc2 :: reboot()

} //  class FakeEc2
