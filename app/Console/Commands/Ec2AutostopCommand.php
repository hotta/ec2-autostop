<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Ec2Factory;
use App\Manual;
use Illuminate\Support\Facades\Artisan;

class Ec2AutostopCommand extends Ec2Command
{
  /**
   * コンソールコマンド書式定義
   *
   * @var string
   */
  protected $name = 'ec2:autostop';

  /**
   * コンソールコマンドの説明
   *
   * @var string
   */
  protected $description = 'インスタンスの自動停止制御';

  /**
   * 対象インスタンスの情報（persistent=false なものすべて）
   *
   * @var Array
   * @contains nickname, private_ip, state, instance_id
   */
  protected $instanceInfoAll = [];

  /**
   * コマンドインスタンスの生成
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();
  }

  /**
   * 停止可能で、停止予定時刻を過ぎていて、かつ manuals レコードがない
   * （手動モードでない）インスタンスをすべて停止する。
   *
   * @return mixed
   */
  public function handle()
  {
    $ec2 = new Ec2Factory;
    $manual = new Manual;
    $ec2->get();
    $list = $ec2->get_instanceList();
    $today = date('Y-m-d');
    foreach ($list as $instance)  {
      if ($instance['terminable'] != 'true' ||
          $instance['state'] != 'running'   ||
         !preg_match('/^\d+:\d+(:\d+)?$/', $instance['stop_at'])) {
        continue;
      }
      $stop_at = strtotime($today . ' ' . $instance['stop_at']);
      if ($stop_at < time())  {
        $entry = $manual->where([
                            [ 't_date',   $today ],
                            [ 'nickname', $instance['nickname'] ],
                          ])->first();
       if (!$entry)  {
          Artisan::call('ec2:stop', [ '-i' => $instance['instance_id'] ]);
       }
      }
    }
  }

} //  Class Ec2AutostopCommand extends Ec2Command
