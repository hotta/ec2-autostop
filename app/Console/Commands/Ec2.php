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
   * インスタンス一覧
   *
   * @var Array
   * @keys : nickname, description, terminable, stop_at, private_ip, 
   *    state, instance_id
   */
  private $instanceList = [];

  /**
   * コマンドインスタンスの生成
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();

      $this->ec2client = AWS::createClient('ec2');

      $di = $this->ec2client->DescribeInstances();
      $this->instanceList = $di->search(
        'Reservations[*].Instances[].{
          nickname:     Tags[*] | [?Key==`Name`].Value | [0],
          description:  Tags[*] | [?Key==`Description`].Value | [0],
          terminable:   Tags[*] | [?Key==`Terminable`].Value | [0],
          stop_at:      Tags[*] | [?Key==`at`].Value | [0],
          private_ip:   NetworkInterfaces[0].PrivateIpAddress,
          state:        State.Name,
          instance_id:  InstanceId
        }'
      );
      usort($this->instanceList, 'self::compare_func');
//  dd($this->instanceList);
  } //  Ec2 :: __construct()

  /**
   * sort 比較関数
   *
   * @return integer
   */
  private function compare_func($a, $b)  {
    return strcmp($a['nickname'], $b['nickname']);
  } //  Ec2List :: compare_func()

  /**
   * インスタンスIDによる対象インスタンスの取得
   *
   * @param String $instanceId
   * @return array
   */
  private function getInstanceById($instanceId)
  {
    for ($i=0; $i<count($this->instanceList); $i++) {
      if ($this->instanceList[$i]['instance_id'] == $instanceId)  {
        return  $this->instanceList[$i];
      }
    }
    return null; //  No such instance
  } //  Ec2 :: getInstanceById()

  /**
   * タグ名による対象インスタンスの取得
   *
   * @param String $nickname
   * @return array
   */
  private function getInstanceByName($nickname)
  {
    for ($i=0; $i<count($this->instanceList); $i++) {
      if ($this->instanceList[$i]['nickname'] == $nickname) {
        return  $this->instanceList[$i];
      }
    }
    return nul; //  No such name
  } //  Ec2 :: getInstanceByName()

  /**
   * インスタンス一覧の取得
   *
   * @return void
   */
  public function getInstanceList()
  {
    return $this->instanceList;
  }

  /**
   * 停止可能インスタンス一覧の取得
   *
   * @return array
   */
  public function getTerminables()
  {
    $ret = [];
    for ($i=0; $i<count($this->instanceList); $i++) {
      if (strtolower($this->instanceList[$i]['terminable']) == 'true')  {
        $ret[] = $this->instanceList[$i];
      }
    }
    return $ret;
  } //  Ec2 :: getTerminables()

  /**
   * 制御対象インスタンスの取得
   *
   * @return array
   */
  public function getInstanceInfo()
  {
    $instance_id = $this->option('instanceid');  //  コマンドラインより
    $nickname = $this->option('nickname');
    if (!$instance_id && !$nickname)  {
      $this->error_exit(
        'インスタンスIDかタグ名のいずれかを指定してください。');
    }
    if ($instance_id && $nickname)  {
      $this->error_exit(
        'インスタンスIDとタグ名は、いずれか１つを指定してください。');
    }
    if ($instance_id) {
      if (! $entry = $this->getInstanceById($instance_id))  {
        $this->error_exit("インスタンスID $instance_id が見つかりません。");
      }
    } else  {
      if (! $entry = $this->getInstanceByName($nickname))  {
        $this->error_exit("サーバー名 $nickname が見つかりません。");
      }
    }
//  dd($entry);
    return $entry;
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

} //  class Ec2 extends Command
