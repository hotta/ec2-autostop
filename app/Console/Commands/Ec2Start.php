<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use AWS;

class Ec2Start extends Ec2
{
  /**
   * コンソールコマンドのシグニチャー（コマンド書式定義）
   *
   * @var string
   */
  protected $signature = 'ec2:start
    {--instanceid= : 開始するインスタンスのインスタンスID}
    {--tagname=    : 開始するインスタンスのタグ名（これらのいずれかを指定）}';

  /**
   * コンソールコマンドの説明
   *
   * @var string
   */
  protected $description = 'インスタンスを開始します';

  /**
   * コマンドインスタンスの生成
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();
  } //  Ec2Start :: __construct()

  /**
   * コンソールコマンドの実行
   *
   * @return mixed
   */
  public function handle()
  {
    $this->getInstanceInfo();
    $this->checkInstanceState();
    $instance_id = $this->instanceInfo['instance_id'];

    $ret = $this->ec2client->startInstances([
      'InstanceIds' => [ $instance_id ]
    ]);
    if ($this->option('verbose')) {
      $this->info("$instance_id を起動しました。\n");
    }
  } //  Ec2Start :: handle()

  /**
   * インスタンス状態の整合チェック
   *
   * @return void
   */
  public function checkInstanceState()
  {
    $id = $this->instanceInfo['instance_id'];
    switch ($this->instanceInfo['state'])  {
    case  'pending':
      $this->error_exit("$id は起動処理中です");
    case  'running':
      $this->error_exit("$id はすでに実行中です");
    case  'shutting-down':
      $this->error_exit("$id はシャットダウン中です");
    case  'terminated':
      $this->error_exit("$id は削除済みです");
    case  'stopping':
      $this->error_exit("$id は停止処理中です");
    case  'stopped':
    default:
      break;
    }
  } //  Ec2Start :: checkInstanceState()

} //  class Ec2Start
