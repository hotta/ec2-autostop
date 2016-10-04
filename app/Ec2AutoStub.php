<?php

namespace App;

use Illuminate\Support;
use App\FakeEc2;

class Ec2AutoStub
{
  /**
   * スタブモデルのインスタンス
   *
   * @return bool
   */
  private $ec2;

  /**
   * コンストラクタ
   *
   * @return void
   */
  public function __construct()
  {
    $this->ec2 = new FakeEc2;
  } //  Ec2AutoStub :: __construct()

  /**
   * レコード一覧の取得（標準モデル関数）
   *
   * @return array
   */
  public function all()
  {
    return $this->ec2->all();
  } //  Ec2AutoStub :: all()

  /**
   * インスタンスの起動
   *
   * @return bool
   */
  public function start($instance_id)
  {
    \Log::info(sprintf("%s::%s(%s) called.",
      __CLASS__, __METHOD__, $instance_id));
    if (!$this->ec2->find($instance_id))  {
      return false;
    }
    $this->ec2->state = 'pending';        //  起動処理中
    $this->ec2->save();
    return  true;
  } //  Ec2AutoStub :: start()

  /**
   * インスタンスの停止
   *
   * @return bool
   */
  public function stop($instance_id)
  {
    \Log::info(sprintf("%s::%s(%s) called.",
      __CLASS__, __METHOD__, $instance_id));
    if (!$this->ec2->find($instance_id))  {
      return false;
    }
    $this->ec2->state = 'stopping';       //  停止処理中
    $this->ec2->save();
    return  true;
  } //  Ec2AutoStub :: stop()

  /**
   * インスタンスの再起動
   *
   * @return bool
   */
  public function reboot($instance_id)
  {
    \Log::info(sprintf("%s::%s(%s) called.",
      __CLASS__, __METHOD__, $instance_id));
    if (!$this->ec2->find($instance_id))  {
      return false;
    }
    $this->ec2->state = 'rebooting';      //  再起動中
    $this->ec2->save();
    return  true;
  } //  Ec2AutoStub :: reboot()

} //  class Ec2AutoStub
