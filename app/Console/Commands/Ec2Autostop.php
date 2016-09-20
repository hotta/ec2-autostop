<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use AWS;

class Ec2Autostop extends Ec2
{
  /**
   * コンソールコマンドのシグニチャー（コマンド書式定義）
   *
   * @var string
   */
  protected $signature = 'ec2:autostop';

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
   * コンソールコマンドの実行
   *
   * @return mixed
   */
  public function handle()
  {
    $list = $this->getTerminables();  //  停止可能インスタンス一覧の取得
    dd($list);
  }

} //  Class Ec2Autostop
