<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use AWS;

class Ec2 extends Command
{
  /**
   * Ec2Client クラスのインスタンス
   *
   * @var \Aws\Ec2\Ec2Client
   */
  protected $ec2client;

  /**
   * 対象インスタンスの情報
   *
   * @var Array
   * @contains tagname, private_ip, state, instance_id
   */
  protected $instanceInfo = [];

  /**
   * コマンドインスタンスの生成
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();

      $this->ec2client = AWS::createClient('ec2');
  } //  Ec2 :: __construct()

  /**
   * 対象インスタンス情報の取得
   *
   * @return void
   */
  public function getInstanceInfo()
  {
    $instance_id = $this->option('instanceid');  //  コマンドラインより
    $tagname = $this->option('tagname');
    if (!$instance_id && !$tagname)  {
      $this->error_exit(
        'インスタンスIDとタグ名のいずれかを指定してください。');
    }
    if ($instance_id && $tagname)  {
      $this->error_exit(
        'インスタンスIDとタグ名は、いずれか１つを指定してください。');
    }
    if ($instance_id)  {
      $di = $this->ec2client->DescribeInstances( [
        'InstanceIds' => [ $instance_id ]
      ]);
    } else {
      $di = $this->ec2client->DescribeInstances( [
        'Filters' => [
          [
            'Name'  =>  'tag:Name',     //  タグ名が 'Name'
            'Values'  =>  [ $tagname  ] //  'Name' タグの値
          ]
        ]
      ]);
    }
//  dd($di);
    $this->instanceInfo = $di->search(
      'Reservations[0].Instances[0].{
        tagname:     Tags[*] | [?Key==`Name`].Value | [0],
        private_ip:   NetworkInterfaces[0].PrivateIpAddress,
        state:        State.Name,
        instance_id:  InstanceId
      }'
    );
//  dd($this->instanceInfo);
  } //  Ec2 :: getInstanceInfo()

  /**
   * エラーメッセージを表示して終了
   *
   * @return void
   */
  public function error_exit($error_msg)  {
    $this->error($error_msg);
    exit(1);
  } //  Ec2 :: error_exit()

} //  class Ec2
