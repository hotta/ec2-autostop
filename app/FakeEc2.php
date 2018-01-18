<?php
//
//  FakeEc2 - EC2 シミュレーター
//
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support;
use RuntimeException;
use Illuminate\Database\Query\Builder;
use App\Jobs\ChangeStateJob;
use Illuminate\Foundation\Bus\DispatchesJobs;

class FakeEc2 extends Model
{
  use DispatchesJobs;

  protected $table = 'fake_ec2';          //  実テーブル名（省略可）
  protected $primaryKey = 'instance_id';  //  プライマリキー項目名
  protected $keyType = 'String';          //  主キーは int でない
  public $incrementing = false;           //  主キーは自動増分でない
  public $timestamps = false;   //  自動更新のタイムスタンプ項目あり

  /**
   * EC2 インスタンスの名称変更（テスト用）
   *
   * @param  string  $instance_id
   * @param  string  $nickname
   * @return void
   */
  public function changeNickname($instance_id, $nickname = null)
  {
    $ec2 = $this->find($instance_id);
    if (!$ec2)  {
      throw new RuntimeException('インスタンスが未登録');
    }
    $ec2->attributes['nickname'] = $nickname;
    $ec2->save();
  }

  /**
   * EC2 インスタンスの説明文言変更（テスト用）
   *
   * @param  string  $instance_id
   * @param  string  $description
   * @return void
   */
  public function changeDescription($instance_id, $description = null)
  {
    $ec2 = $this->find($instance_id);
    if (!$ec2)  {
      throw new RuntimeException('インスタンスが未登録');
    }
    $ec2->attributes['description'] = $description;
    $ec2->save();
  }

  /**
   * EC2 インスタンスの状態変更（テスト用）
   *
   * @param  string  $instance_id
   * @param  string  $state
   * @return void
   */
  public function changeState($instance_id, $state = 'unKnown')
  {
    $ec2 = $this->find($instance_id);
    if (!$ec2)  {
      throw new RuntimeException('インスタンスが未登録');
    }
    $ec2->attributes['state'] = $state;
    $ec2->save();
  }

  /**
   * 「終了可能」フラグの変更（テスト用）
   *
   * @param  string  $instance_id
   * @param  bool    $state
   * @return void
   */
  public function changeTerminable($instance_id, $state)
  {
    $ec2 = $this->find($instance_id);
    if (!$ec2)  {
      throw new RuntimeException('インスタンスが未登録');
    }
    $ec2->attributes['terminable'] = $state;
    $ec2->save();
  }

  /**
   * 終了予定時刻の変更（テスト用）
   *
   * @param  string  $instance_id
   * @param  string  $time
   * @return void
   */
  public function changeStopAt($instance_id, $time)
  {
    $ec2 = $this->find($instance_id);
    if (!$ec2)  {
      throw new RuntimeException('インスタンスが未登録');
    }
    $ec2->attributes['stop_at'] = $time;
    $ec2->save();
  }

  /**
   * インスタンスの起動
   *
   * @return void
   *
   * @throws RuntimeException
   */
  public function start($instance_id)
  {
    \Log::info(__CLASS__.'::'.__METHOD__.'('.$instance_id.') called.');
    $ec2 = $this->find($instance_id);
    if (!$ec2)  {
      throw new RuntimeException('インスタンスが未登録');
    }
    if ($ec2->attributes['state'] != 'stopped')  {
      throw new RuntimeException('インスタンスの状態が停止中以外');
    }
    $ec2->attributes['state'] = 'pending';             //  起動処理中へ
    $ec2->save();
    //  10秒後に running に遷移
    $this->dispatch(new ChangeStateJob($instance_id, 'running', 10));
  }

  /**
   * インスタンスの停止
   *
   * @return void
   *
   * @throws RuntimeException
   */
  public function stop($instance_id)
  {
    \Log::info(__CLASS__.'::'.__METHOD__.'('.$instance_id.') called.');
    $ec2 = $this->find($instance_id);
    if (!$ec2)  {
      throw new RuntimeException('インスタンスが未登録');
    }
    if ($ec2->attributes['state'] != 'running')  {
      throw new RuntimeException('インスタンスの状態が実行中以外');
    }
    $ec2->attributes['state'] = 'stopping';             //  停止処理中へ
    $ec2->save();
    //  10秒後に stopped に遷移
    $this->dispatch(new ChangeStateJob($instance_id, 'stopped', 10));
  }

  /**
   * インスタンスの再起動
   *
   * @return void
   *
   * @throws RuntimeException
   */
  public function reboot($instance_id)
  {
    \Log::info(__CLASS__.'::'.__METHOD__.'('.$instance_id.') called.');
    $ec2 = $this->find($instance_id);
    if (!$ec2)  {
      throw new RuntimeException('インスタンスが未登録');
    }
    if ($ec2->attributes['state'] == 'running') {
      $ec2->attributes['state'] = 'stopping';     //  停止処理中へ
    } else if ($ec2->attributes['state'] == 'stopped')  {
      $ec2->attributes['state'] = 'pending';      //  起動処理中へ
    } else {
      throw new RuntimeException('インスタンスの状態が実行中／停止済み以外');
    }
    $ec2->save();
    //  10秒後に running に遷移
    $this->dispatch(new ChangeStateJob($instance_id, 'running', 10));
  }
}
