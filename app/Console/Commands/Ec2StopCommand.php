<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use RuntimeException;

class Ec2StopCommand extends Ec2Command
{
  /**
   * コンソールコマンドの名前
   *
   * @var string
   */
  protected $name = 'ec2:stop';

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
  } //  Ec2StopCommand :: __construct()

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
  } //  Ec2StopCommand :: handle()

  /**
   * インスタンス状態の整合チェック
   *
   * @throws RuntimeException 指示がインスタンスの状態と合わない場合
   *
   * @return void
   */
  public function checkInstanceState($info)
  {
    $id = $info['instance_id'];
    $error = null;
    switch ($info['state'])  {
    case  'pending':
      $error = ("インスタンス $id は起動処理中です");
    case  'shutting-down':
      $error = ("インスタンス $id はシャットダウン中です");
    case  'terminated':
      $error = ("インスタンス $id は削除済みです");
    case  'stopping':
      $error = ("インスタンス $id は停止処理中です");
    case  'stopped':
      $error = ("インスタンス $id は停止済みです");
    case  'running':
    default:
      break;
    }
    if ($error) {
      throw new RuntimeException($error);
    }
  } //  Ec2StopCommand :: checkInstanceState()

} //  class Ec2StopCommand extends Ec2Command
