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
//  dd($this->instanceList);
  } //  Ec2Factory :: __construct()

  /**
   * インスタンス一覧の取得
   *
   * @return void
   */
  public function setData()
  {
    if (count($this->instanceList) < 1) {
      $this->get();
    }
  }

  /**
   * 停止可能インスタンス一覧の取得
   *
   * @return array
   */
  public function getTerminables()
  {
    $this->setData();
    $ret = [];
    for ($i=0; $i<count($this->instanceList); $i++) {
      if ($this->instanceList[$i]['terminable'])  {
        $ret[] = $this->instanceList[$i];
      }
    }
    return $ret;
  }

  /**
   * インスタンスIDによる対象インスタンスの取得
   *
   * @param String $instance_id
   * @return array
   */
  public function findByInstanceId($instance_id)
  {
    $this->setData();
    for ($i=0; $i<count($this->instanceList); $i++) {
      if ($this->instanceList[$i]['instance_id'] == $instance_id)  {
        return  $this->instanceList[$i];
      }
    }
    return null; //  No such instance
  }

  /**
   * タグ名による対象インスタンスの取得
   *
   * @param String $nickname
   * @return array
   */
  public function findByNickname($nickname)
  {
    $this->setData();
    for ($i=0; $i<count($this->instanceList); $i++) {
      if ($this->instanceList[$i]['nickname'] == $nickname) {
        return  $this->instanceList[$i];
      }
    }
    return null; //  No such name
  }

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
//  dd($this->instanceList);
  }

  /**
   * 属性データの正規化
   *
   * @return void
   */
  private function normalize()  {

    $collection = collect([ 1, '1', 'true', true ]);

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
      if (is_string($this->instanceList[$i]['terminable'])) {
        $this->instanceList[$i]['terminable'] = 
          strtolower($this->instanceList[$i]['terminable']);
      }
      if ($collection->contains($this->instanceList[$i]['terminable'])) {
        $this->instanceList[$i]['terminable'] = true;
      } else  {
        $this->instanceList[$i]['terminable'] = false;
      }
    }
//  dd($this->instanceList);
  }

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
  }

  /**
   * インスタンスの起動
   *
   * @return bool
   */
  public function start($instance_id)
  {
    return $this->auto->start($instance_id);
  }

  /**
   * インスタンスの停止
   *
   * @return bool
   */
  public function stop($instance_id)
  {
    return  $this->auto->stop($instance_id);
  }

  /**
   * インスタンスの再起動
   *
   * @return bool
   */
  public function reboot($instance_id)
  {
    return $this->auto->reboot($instance_id);
  }

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
  }

  /**
   * Execute the query as a "select" statement.
   *
   * @param  array  $columns
   * @return array|static[]
   */
  public function get($columns = ['*'])
  {
    $this->instanceList = $this->auto->get($columns);
    $this->normalize();
    $this->checkManuals();
    $this->set_state_j();
    return $this;
  }

}
