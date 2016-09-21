<?php

namespace App;

use Illuminate\Support;
use AWS;

class Ec2Auto
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
   * コンストラクタ
   *
   * @return void
   */
  public function __construct()
  {
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
  } //  Ec2Auto :: __construct()

  /**
   * sort 比較関数
   *
   * @return integer
   */
  private function compare_func($a, $b)  {
    return strcmp($a['nickname'], $b['nickname']);
  } //  Ec2Auto :: compare_func()

  /**
   * インスタンスIDによる対象インスタンスの取得
   *
   * @param String $instance_id
   * @return array
   */
  public function getInstanceById($instance_id)
  {
    for ($i=0; $i<count($this->instanceList); $i++) {
      if ($this->instanceList[$i]['instance_id'] == $instance_id)  {
        return  $this->instanceList[$i];
      }
    }
    return null; //  No such instance
  } //  Ec2Auto :: getInstanceById()

  /**
   * タグ名による対象インスタンスの取得
   *
   * @param String $nickname
   * @return array
   */
  public function getInstanceByName($nickname)
  {
    for ($i=0; $i<count($this->instanceList); $i++) {
      if ($this->instanceList[$i]['nickname'] == $nickname) {
        return  $this->instanceList[$i];
      }
    }
    return nul; //  No such name
  } //  Ec2Auto :: getInstanceByName()

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
  } //  Ec2Auto :: getTerminables()

  /**
   * インスタンスの開始
   *
   * @return void
   */
  public function start($instance_id)
  {
    $ret = $this->ec2client->startInstances([
      'InstanceIds' => [ $instance_id ]
    ]);
  } //  Ec2Auto :: start()

  /**
   * インスタンスの停止
   *
   * @return void
   */
  public function stop($instance_id)
  {
    $ret = $this->ec2client->stopInstances([
      'InstanceIds' => [ $instance_id ]
    ]);
  } //  Ec2Auto :: stop()

} //  class Ec2Auto
