<?php

namespace App;

use Illuminate\Support;

class Ec2AutoStub
{
  /**
   * スタブデータ
   */
  const STUB = [
      [
        'nickname'    => 'dev-test1',
        'description' => 'テスト#test1',
        'terminable'  => 'true',
        'stop_at'     => '18:00',
        'private_ip'  => '172.16.1.8',
        'state'       => 'stopped',
        'instance_id' => 'i-0987183xx9ef17d77'
      ], [
        'nickname'    => 'dev-web1',
        'description' => 'テスト#2評価用',
        'terminable'  => 'true',
        'stop_at'     => '18:00',
        'private_ip'  => '172.16.0.8',
        'state'       => 'running',
        'instance_id' => 'i-00c3eaeb0xxx8a242'
      ], [
        'nickname'    => 'dev-dummy1',
        'description' => 'ダミー（24h運用）',
        'terminable'  => 'false',
  //    'stop_at'     => '18:00',
        'private_ip'  => '172.16.0.8',
        'state'       => 'running',
        'instance_id' => 'i-xxc3eaeb0426xx242'
      ], [
        'nickname'    => 'dev-dummy2',
        'description' => 'ダミー#2',
        'terminable'  => 'true',
        'stop_at'     => '12:00',
        'private_ip'  => '172.16.0.99',
        'state'       => 'pending',
        'instance_id' => 'i-00xxeaeb042XXa242'
      ], [
        'nickname'    => 'dev-dummy3',
        'description' => 'ダミー#3',
        'terminable'  => 'true',
        'stop_at'     => '12:00',
        'private_ip'  => '172.16.0.99',
        'state'       => 'stopping',
        'instance_id' => 'i-00c3xxeb042XXa242'
      ], [
        'nickname'    => 'dev-dummy4',
        'description' => 'ダミー#4',
        'terminable'  => 'true',
        'stop_at'     => '12:00',
        'private_ip'  => '172.16.0.99',
        'state'       => 'shutting-down',
        'instance_id' => 'i-00c3eaxx042XXa242'
      ], [
        'nickname'    => 'dev-dummy5',
        'description' => 'ダミー#5',
        'terminable'  => 'true',
        'stop_at'     => '12:00',
        'private_ip'  => '172.16.0.99',
        'state'       => 'terminated',
        'instance_id' => 'i-00c3eaeb042XXa242'
      ],
    ];

  /**
   * レコード一覧の取得（標準モデル関数）
   *
   * @return array
   */
  public function all()
  {
    return  self::STUB;
  } //  Ec2AutoStub :: all()

  /**
   * インスタンスの開始
   *
   * @return void
   */
  public function start($instance_id)
  {
    \Log::info(sprintf("%s::%s(%s) called.", __CLASS__, __METHOD__, $instance_id));
  } //  Ec2AutoStub :: start()

  /**
   * インスタンスの停止
   *
   * @return void
   */
  public function stop($instance_id)
  {
    \Log::info(sprintf("%s::%s(%s) called.", __CLASS__, __METHOD__, $instance_id));
  } //  Ec2AutoStub :: stop()

} //  class Ec2AutoStub
