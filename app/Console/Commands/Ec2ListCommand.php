<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Ec2Factory;

class Ec2ListCommand extends Ec2Command
{
  /**
   * コンソールコマンドの書式
   *
   * @var string
   */
  protected $name = 'ec2:list';

  /**
   * コンソールコマンドの説明
   *
   * @var string
   */
  protected $description = 'EC2 インスタンスの一覧を表示します';

  /**
   * コンソールコマンドのオプション定義
   *
   * @return mixed
   */
  protected function getOptions()
  {
    return  [];
  }

  /**
   * コマンドインスタンスの生成
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();
  } //  Ec2ListCommand :: __construct()

  /**
   * コンソールコマンドの実行
   *
   * @return mixed
   */
  public function handle()
  {
    $ec2 = new Ec2Factory;
    $headers = [ 'Nickname', 'Private IP', 'Status', 'Instance ID', 'Stop at',
      'Term' ];
    $filtered = $ec2->orderBy('nickname')->get();
    $i = 0;
    foreach ($filtered as $i => $e)  {
      $instances[$i]['nickname']    = $e['nickname'];
      $instances[$i]['private_ip']  = $e['private_ip'];
      $instances[$i]['state']       = $e['state'];
      $instances[$i]['instance_id'] = $e['instance_id'];
      $instances[$i]['stop_at']     = $e['stop_at'];
      $instances[$i]['terminable']  = $e['terminable'];
      $i++;
    }
    $this->table($headers, $instances);
  } //  Ec2ListCommand :: handle()

} //  class Ec2ListCommand extends Ec2Command
