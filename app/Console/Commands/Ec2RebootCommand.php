<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use RuntimeException;

class Ec2RebootCommand extends Ec2Command
{
  /**
   * コンソールコマンドの書式
   *
   * @var string
   */
  protected $name = 'ec2:reboot';

  /**
   * コンソールコマンドの説明
   *
   * @var string
   */
  protected $description = 'インスタンスを再起動します';

  /**
   * コマンドインスタンスの生成
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();
  } //  Ec2RebootCommand :: __construct()

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
    $this->ec2->reboot($instance_id);
    if ($this->getOutput()->isVerbose()) {
      $this->info(sprintf("%s(%s)を再起動しました。",
        $nickname, $instance_id));
    }
  } //  Ec2RebootCommand :: handle()

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
      $error = "インスタンス $id は起動処理中です";
      break;
    case  'shutting-down':
      $error = "インスタンス $id はシャットダウン中です";
      break;
    case  'terminated':
      $error = "インスタンス $id は削除済みです";
      break;
    case  'stopping':
      $error = "インスタンス $id は停止処理中です";
      break;
    case  'stopped':
    case  'running':
    default:
      break;
    }
    if ($error) {
      throw new RuntimeException($error);
    }
  } //  Ec2RebootCommand :: checkInstanceState()

} //  class Ec2RebootCommand extends Ec2Command
