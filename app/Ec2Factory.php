<?php

namespace App;

use Illuminate\Support;
use App\FakeEc2;
use App\Ec2Ctrl;

class Ec2Factory
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
      $this->auto = new FakeEc2;    //  モデルでシミュレート
    } else  {
      $this->auto = new Ec2Ctrl;    //  AWS API をコール
    }
    $this->instanceList = $this->auto->orderBy('nickname')->get();
    $this->normalize();
    $this->checkManuals();
    $this->set_state_j();
//  dd($this->instanceList);
  } //  Ec2Factory :: __construct()

  /**
   * レコード一覧の取得（標準モデル関数）
   *
   * @return array
   */
  public function all()
  {
    return $this->instanceList;
  } //  Ec2Factory :: all()

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
  } //  Ec2Factory :: getTerminables()

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
  } //  Ec2Factory :: findByInstanceId()

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
  } //  Ec2Factory :: findByNickname()

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
  } //  Ec2Factory :: set_state_j()

  /**
   * 属性データの正規化
   *
   * @return void
   */
  private function normalize()  {
    for ($i=0; $i<count($this->instanceList); $i++)  {
      foreach ([ 
        'nickname',     //  タグ名
        'state',        //  AWS API 戻り値
        'instance_id',  //  AWS API 戻り値
        'private_ip'    //  AWS API 戻り値
      ] as $key)  {
        if (!isset($this->instanceList[$i][$key])) {
          \Log::error(sprintf("%s::%s() called. '%s' for '%s' not set.",
            __CLASS__, __METHOD__, studly_case($key), 
            $this->instanceList[$i]['nickname']));
          abort(503);  //  必須パラメーター
        }
      }
      foreach ([
        'description',  //  タグ名
        'stop_at',      //  タグ名
        'terminable',   //  タグ名
      ] as $key)  {
        if (!isset($this->instanceList[$i][$key])) {
          $this->instanceList[$i][$key] = ''; //  任意パラメーター
        }
      }
    }
//  dd($this->instanceList);
  } //  Ec2Factory :: normalize()

  /**
   * レコードが存在したら手動モードに変更
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
  } //  Ec2Factory :: checkManuals()

  /**
   * インスタンスの起動
   *
   * @return bool
   */
  public function start($instance_id)
  {
    return $this->auto->start($instance_id);
  } //  Ec2Factory :: start()

  /**
   * インスタンスの停止
   *
   * @return bool
   */
  public function stop($instance_id)
  {
    return  $this->auto->stop($instance_id);
  } //  Ec2Factory :: stop()

  /**
   * インスタンスの再起動
   *
   * @return bool
   */
  public function reboot($instance_id)
  {
    return $this->auto->reboot($instance_id);
  } //  Ec2Factory :: reboot()

  /**
   * Add an "order by" clause to the query.
   *
   * @param  string  $column
   * @param  string  $direction
   * @return Illuminate\Database\Query\Builder
   */
  public function orderBy($column, $direction = 'asc')
  {
    return $this->auto->orderBy($column, $direction);
  } //  Ec2Factory :: orderBy()

  /**
   * Execute the query as a "select" statement.
   *
   * @param  array  $columns
   * @return array|static[]
   */
  public function get($columns = ['*'])
  {
    return $this->auto->get($columns);
  }

} //  class Ec2Factory
