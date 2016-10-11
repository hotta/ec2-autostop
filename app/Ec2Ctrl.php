<?php
//
//  実際に AWS API の呼び出しを行う
//
namespace App;

use Illuminate\Support;
use AWS;

class Ec2Ctrl
{
  /**
   * Ec2Client クラスのインスタンス
   *
   * @var \Aws\Ec2\Ec2Client
   */
  private $ec2client;

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
    $this->getInstanceList();
  } //  Ec2Ctrl :: __construct()

  /**
   * レコード一覧の取得（標準モデル関数）
   *
   * @return array
   */
  public function orderBy()
  {
    usort($this->instanceList, 'self::compare_func');
    return $this;
  } //  Ec2Ctrl :: all()

  /**
   * レコード一覧の取得（標準モデル関数）
   *
   * @return array
   */
  public function get()
  {
    return $this->instanceList;
  } //  Ec2Ctrl :: all()

  /**
   * インスタンス一覧の取得
   *
   * @return void
   */
  private function getInstanceList()
  {
    $di = $this->ec2client->DescribeInstances();
    $this->instanceList = $di->search(
      'Reservations[*].Instances[].{
        nickname:     Tags[*] | [?Key==`Name`].Value | [0],
        description:  Tags[*] | [?Key==`Description`].Value | [0],
        terminable:   Tags[*] | [?Key==`Terminable`].Value | [0],
        stop_at:      Tags[*] | [?Key==`Stop_at`].Value | [0],
        private_ip:   NetworkInterfaces[0].PrivateIpAddress,
        state:        State.Name,
        instance_id:  InstanceId
      }'
    );
  } //  Ec2Ctrl :: getInstanceList()

  /**
   * sort 比較関数
   *
   * @return integer
   */
  private function compare_func($a, $b)  {
    return strcmp($a['nickname'], $b['nickname']);
  } //  Ec2Ctrl :: compare_func()

  /**
   * インスタンスの起動
   *
   * @return void
   */
  public function start($instance_id)
  {
    \Log::info(__CLASS__.'::'.__METHOD__.'('.$instance_id.') called.');
    $this->ec2client->startInstances([ 'InstanceIds' => [ $instance_id ] ]);
  } //  Ec2Ctrl :: start()

  /**
   * インスタンスの停止
   *
   * @return bool
   */
  public function stop($instance_id)
  {
    \Log::info(__CLASS__.'::'.__METHOD__.'('.$instance_id.') called.');
    $this->ec2client->stopInstances([ 'InstanceIds' => [ $instance_id ] ]);
  } //  Ec2Ctrl :: stop()

  /**
   * インスタンスの再起動
   *
   * @return bool
   */
  public function reboot($instance_id)
  {
    \Log::info(__CLASS__.'::'.__METHOD__.'('.$instance_id.') called.');
    $this->ec2client->rebootInstances([ 'InstanceIds' => [ $instance_id ] ]);
  } //  Ec2Ctrl :: reboot()

} //  class Ec2Ctrl
