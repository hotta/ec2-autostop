<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Ec2Factory;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputOption;

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
   * 冗長表示モード
   *
   * @var bool
   */
  protected $verbose = false;

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
    if ($this->getOutput()->isVerbose()) {
      $this->verbose = true;
    }
    $ec2 = new Ec2Factory;
    $ec2->get();
    $list = $ec2->get_instanceList();
    $today = date('Y-m-d');
    foreach ($list as $instance)  {
      $nickname = $instance['nickname'];
      if (!$instance['terminable'])  {
        if ($this->verbose) {
          $this->info($nickname . ' is not terminable. Skipping..');
        }
        continue;
      }
      if ($instance['state'] != 'running')  {
        if ($this->verbose) {
          $this->info($nickname . ' is not runnging. Skipping..');
        }
        continue;
      }
      if (!preg_match('/^\d+:\d+(:\d+)?$/', $instance['stop_at'])) {
        if ($this->verbose) {
          $this->info(sprintf("%s: stop_at=\"%s\". Skipping..",
            $nickname, $instance['stop_at']));
        }
        continue;
      }
      $stop_at = strtotime($today . ' ' . $instance['stop_at']);
      if ($stop_at < time())  {
        Artisan::call('ec2:stop', [ '-i' => $instance['instance_id'] ]);
        if ($this->verbose) {
          $this->info($nickname . ' Stopped.');
        }
      } else  {
        if ($this->verbose) {
          $this->info($nickname . ' stop_at > now. Skipping...');
        }
      }
    }
  }

} //  Class Ec2AutostopCommand extends Ec2Command
