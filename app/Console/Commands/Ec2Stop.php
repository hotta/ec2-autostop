<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Ec2AutoFactory;

class Ec2Stop extends Ec2
{
  /**
   * コンソールコマンドのシグニチャー（コマンド書式定義）
   *
   * @var string
   */
  protected $signature = 'ec2:stop
  {--instanceid= : 停止するインスタンスのインスタンスID}
  {--nickname=   : 停止するインスタンスのニックネーム（これらのいずれかを指定）}';

  /**
   * コンソールコマンドの説明
   *
   * @var string
   */
  protected $description = 'インスタンスを停止します';

  /**
   * コマンドインスタンスの生成
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();
  } //  Ec2Stop :: __construct()

  /**
   * コンソールコマンドの実行
   *
   * @return mixed
   */
  public function handle()
  {
    $info = $this->getInstanceInfo();
    $this->checkInstanceState($info);
    $nickname = $info['nickname'];
    $instance_id = $info['instance_id'];
    $this->ec2->stop($instance_id);
    if ($this->option('verbose')) {
      $this->info(sprintf("%s(%s)を停止しました。", 
        $nickname, $instance_id));
    }
  } //  Ec2Stop :: handle()

  /**
   * インスタンス状態の整合チェック
   *
   * @return void
   */
  public function checkInstanceState($info)
  {
    $id = $info['instance_id'];
    switch ($info['state'])  {
    case  'pending':
      dd("$id は起動処理中です");
    case  'shutting-down':
      dd("$id はシャットダウン中です");
    case  'terminated':
      dd("$id は削除済みです");
    case  'stopping':
      dd("$id は停止処理中です");
    case  'stopped':
      dd("$id は停止済みです");
    case  'running':
    default:
      break;
    }
  } //  Ec2Stop :: checkInstanceState()

} //  class Ec2Stop
