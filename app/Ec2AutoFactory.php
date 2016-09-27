<?php

namespace App;

use Illuminate\Support;

class Ec2AutoFactory
{
  /**
   * 実クラスのインスタンス
   *
   * @var 
   */
  protected $auto;

  /**
   * インスタンス一覧
   *
   * @var Array
   * @keys : nickname, description, terminable, stop_at, private_ip,
   *    state, instance_id
   */
  protected $instanceList = [];

  /**
   * コンストラクタ
   *
   * @return void
   */
  public function __construct()
  {
    if (env('AWS_EC2_STUB')) {
      $this->auto = new Ec2AutoStub;
    } else  {
      $this->auto = new Ec2AutoProd;
    }
    $this->instanceList = $this->auto->all();
    $this->normalize();
    $this->checkManuals();
    $this->set_state_j();
//  dd($this->instanceList);
  } //  Ec2AutoFactory :: __construct()

  /**
   * レコード一覧の取得（標準モデル関数）
   *
   * @return array
   */
  public function all()
  {
    return $this->instanceList;
  } //  Ec2AutoFactory :: all()

  /**
   * 停止可能インスタンス一覧の取得
   *
   * @return array
   */
  public function getTerminables()
  {
    $ret = [];
    for ($i=0; $i<count($this->instanceList); $i++) {
      if (strtolower($this->instanceList[$i]['terminable']) == 'true')  {
        $ret[] = $this->instanceList[$i];
      }
    }
    return $ret;
  } //  Ec2AutoFactory :: getTerminables()

  /**
   * インスタンスIDによる対象インスタンスの取得
   *
   * @param String $instance_id
   * @return array
   */
  public function findByInstanceId($instance_id)
  {
    for ($i=0; $i<count($this->instanceList); $i++) {
      if ($this->instanceList[$i]['instance_id'] == $instance_id)  {
        return  $this->instanceList[$i];
      }
    }
    return null; //  No such instance
  } //  Ec2AutoFactory :: findByInstanceId()

  /**
   * タグ名による対象インスタンスの取得
   *
   * @param String $nickname
   * @return array
   */
  public function findByNickname($nickname)
  {
    for ($i=0; $i<count($this->instanceList); $i++) {
      if ($this->instanceList[$i]['nickname'] == $nickname) {
        return  $this->instanceList[$i];
      }
    }
    return null; //  No such name
  } //  Ec2AutoFactory :: findByNickname()

  /**
   * 表示用日本語ステータスのセット
   *
   * @return void
   */
  private function set_state_j()  {
    for ($i=0; $i<count($this->instanceList); $i++)  {
      switch ($this->instanceList[$i]['state'])  {
      case 'pending':
        $this->instanceList[$i]['state_j'] = '起動処理中';
        break;
      case 'running':
        $this->instanceList[$i]['state_j'] = '動作中';
        break;
      case 'rebooting':
        $this->instanceList[$i]['state_j'] = '再起動中';
        break;
      case 'stopping':
        $this->instanceList[$i]['state_j'] = '停止処理中';
        $this->instanceList[$i]['stop_at'] = '';
        break;
      case 'stopped':
        $this->instanceList[$i]['state_j'] = '停止済み';
        $this->instanceList[$i]['stop_at'] = '';
        break;
      case 'shutting-down':
        $this->instanceList[$i]['state_j'] = '削除処理中';
        $this->instanceList[$i]['stop_at'] = '';
        break;
      case 'terminated':
        $this->instanceList[$i]['state_j'] = '削除済み';
        $this->instanceList[$i]['stop_at'] = '';
        break;
      }
    }
  } //  Ec2AutoFactory :: set_state_j()

  /**
   * 属性データの正規化
   *
   * @return void
   */
  private function normalize()  {
    for ($i=0; $i<count($this->instanceList); $i++)  {
      foreach ([ 
        'nickname',     //  タグ名
        'terminable',   //  タグ名
        'state',        //  AWS API 戻り値
        'instance_id'   //  AWS API 戻り値
      ] as $key)  {
        if (!isset($this->instanceList[$i][$key])) {
          \Log::error(sprintf("%s::%s() called. '%s' not set.",
            __CLASS__, __METHOD__, $key));    //  必須パラメーター
          abort(503);
        }
      }
      foreach ([
        'description',  //  タグ名
        'stop_at',      //  タグ名
        'private_ip'    //  AWS API 戻り値
      ] as $key)  {
        if (!isset($this->instanceList[$i][$key])) {
          $this->instanceList[$i][$key] = ''; //  任意パラメーター
        }
      }
    }
//  dd($this->instanceList);
  } //  Ec2AutoFactory :: normalize()

  /**
   * レコードが存在したら手動モードへ
   *
   * @return void
   */
  private function checkManuals()
  {
    $manuals = Manual::where('t_date', date('Y-m-d'))
                        ->get();              //  本日分レコード取得
    foreach ($manuals as $manual) {
      for ($i=0; $i<count($this->instanceList); $i++) {
        if ($this->instanceList[$i]['instance_id'] == $manual->instance_id)  {
          $this->instanceList[$i]['stop_at'] = 'manual';  //  手動モード
          break;
        }
      }
    }
  } //  Ec2AutoFactory :: checkManuals()

  /**
   * インスタンスの起動
   *
   * @return void
   */
  public function start($instance_id)
  {
    $this->auto->start($instance_id);
  } //  Ec2AutoFactory :: start()

  /**
   * インスタンスの停止
   *
   * @return void
   */
  public function stop($instance_id)
  {
    $this->auto->stop($instance_id);
  } //  Ec2AutoFactory :: stop()

  /**
   * インスタンスの再起動
   *
   * @return void
   */
  public function reboot($instance_id)
  {
    $this->auto->reboot($instance_id);
  } //  Ec2AutoFactory :: reboot()

} //  class Ec2AutoFactory
