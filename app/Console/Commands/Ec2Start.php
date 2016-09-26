<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Ec2Start extends Ec2
{
  /**
   * コンソールコマンドのシグニチャー（コマンド書式定義）
   *
   * @var string
   */
  protected $signature = 'ec2:start
    {--instanceid= : 開始するインスタンスのインスタンスID}
    {--nickname=   : 開始するインスタンスのニックネーム（これらのいずれかを指定）}';

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
    $info = $this->getInstanceInfo();
    $this->checkInstanceState($info);
    $nickname = $info['nickname'];
    $instance_id = $info['instance_id'];
    $this->ec2->start($instance_id);
    if ($this->option('verbose')) {
      $this->info(sprintf("%s(%s)を起動しました。",
        $nickname, $instance_id));
    }
  } //  Ec2Start :: handle()

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
    case  'running':
      dd("$id はすでに実行中です");
    case  'shutting-down':
      dd("$id はシャットダウン中です");
    case  'terminated':
      dd("$id は削除済みです");
    case  'stopping':
      dd("$id は停止処理中です");
    case  'stopped':
    default:
      break;
    }
  } //  Ec2Start :: checkInstanceState()

} //  class Ec2Start
