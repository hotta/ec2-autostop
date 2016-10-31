<?php

namespace App;

use Illuminate\Support;
use App\FakeEc2;
use App\Ec2Ctrl;
use Illuminate\Database\Eloquent\Collection as Collection;

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

  public function get_instanceList() {
    return  $this->instanceList;
  }

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
        break;
      case 'stopped':
        $this->instanceList[$i]['state_j'] = '停止済み';
        break;
      case 'shutting-down':
        $this->instanceList[$i]['state_j'] = '削除処理中';
        break;
      case 'terminated':
        $this->instanceList[$i]['state_j'] = '削除済み';
        break;
      }
    }
  }

  /**
   * 属性データの正規化
   *
   * @return void
   */
  private function normalize()  {
    $this->normalize_mandatory();
    $this->normalize_description();
    $this->normalize_stop_at();
    $this->normalize_terminable();
  }

  /**
   * 属性データの正規化（必須項目）
   *
   * @return void
   */
  private function normalize_mandatory()  {

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
            $this->instanceList[$i]['instance_id']));
          abort(503);  //  必須パラメーター
        }
      }
    }
  }

  /**
   * 属性データの正規化（説明）
   *
   * @return void
   */
  private function normalize_description()  {

    for ($i=0; $i<count($this->instanceList); $i++)  {
      if (!isset($this->instanceList[$i]['description'])) {
        $this->instanceList[$i]['description'] = '';
      }
    }
  }

  /**
   * 属性データの正規化（停止時刻）
   *
   * @return void
   */
  private function normalize_stop_at()  {

    $collection = collect([ 'pending', 'running' ]);

    for ($i=0; $i<count($this->instanceList); $i++)  {
      if (!isset($this->instanceList[$i]['stop_at'])  ||
        $this->instanceList[$i]['stop_at'] === '') {
        $this->instanceList[$i]['stop_at'] = '';
      } else if (!preg_match('/^[12]?[0-9]:[0-5]?[0-9](:[0-5]?[0-9])$/', 
          $this->instanceList[$i]['stop_at']))  {
          \Log::error(sprintf("stop_at format error : %s", 
            $this->instanceList[$i]['stop_at']));
          abort(503);  //  必須パラメーター
      }
      if (! $collection->contains($this->instanceList[$i]['state']))  {
        $this->instanceList[$i]['stop_at'] = 'manual';
      }
    }
  }

  /**
   * 属性データの正規化（停止可能）
   *
   * @return void
   */
  private function normalize_terminable()  {

    $collection = collect([ '1', 'true' ]);

    for ($i=0; $i<count($this->instanceList); $i++)  {
      if (!isset($this->instanceList[$i]['terminable'])) {
        $this->instanceList[$i]['terminable'] = false;
      } else if (is_string($this->instanceList[$i]['terminable'])) {
        $this->instanceList[$i]['terminable'] = 
          strtolower($this->instanceList[$i]['terminable']);
      }
      if ($collection->contains($this->instanceList[$i]['terminable'])) {
        $this->instanceList[$i]['terminable'] = true;
      } else  {
        $this->instanceList[$i]['terminable'] = false;
      }
    }
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
   * Execute the query as a "select" statement.
   *
   * @param  array  $columns
   * @return array|static[]
   */
  public function get($columns = ['*'])
  {
    $list = $this->auto->orderBy('nickname')->get($columns);
    if ($list instanceof Collection) {
      $i = 0;
      foreach ($list as $key => $server) {
        $this->instanceList[$i]['nickname'] = $server->nickname;
        $this->instanceList[$i]['instance_id'] = $server->instance_id;
        $this->instanceList[$i]['description'] = $server->description;
        $this->instanceList[$i]['terminable'] = $server->terminable;
        $this->instanceList[$i]['stop_at'] = $server->stop_at;
        $this->instanceList[$i]['private_ip'] = $server->private_ip;
        $this->instanceList[$i]['state'] = $server->state;
        $i++;
      }
    } else {
        $this->instanceList = $list;
    }
    $this->normalize();
    $this->checkManuals();
    $this->set_state_j();
    return $this;
  }

}
