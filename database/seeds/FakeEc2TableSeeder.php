<?php

use Illuminate\Database\Seeder;

class FakeEc2TableSeeder extends Seeder
{
    /**
     * データベース初期値設定実行
     * php artisan db:seed または tests からの $this->seed() で呼ばれる
     *
     * @return void
     */
    public function run()
    {
      DB::table('fake_ec2')->insert([
        [
          'nickname'    => 'dev1',
          'instance_id' => 'i-dev1',
          'description' => 'ダミー#1',
          'terminable'  => true,
          'stop_at'     => '14:00',
          'private_ip'  => '172.16.0.8',
          'state'       => 'running',
        ], [
          'nickname'    => 'dev2',
          'instance_id' => 'i-dev2',
          'description' => 'ダミー#2',
          'terminable'  => false,
          'stop_at'     => '15:00',
          'private_ip'  => '172.16.0.99',
          'state'       => 'running',
        ], [
          'nickname'    => 'dev3',
          'instance_id' => 'i-dev3',
          'description' => 'ダミー#3',
          'terminable'  => 'true',
          'stop_at'     => '16:00',
          'private_ip'  => '172.16.0.99',
          'state'       => 'running',
        ], [
          'nickname'    => 'dev4',
          'instance_id' => 'i-dev4',
          'description' => 'ダミー#4',
          'terminable'  => 'false',
          'stop_at'     => '17:00',
          'private_ip'  => '172.16.0.99',
          'state'       => 'running',
        ], [
          'nickname'    => 'dev5',
          'instance_id' => 'i-dev5',
          'description' => 'ダミー#5',
          'terminable'  => 'null',
          'stop_at'     => '18:00',
          'private_ip'  => '172.16.0.99',
          'state'       => 'running',
        ],[
          'nickname'    =>  'dev-test1',
          'instance_id' =>  'i-dev-test1',
          'description' =>  'テスト#test1（24時間運用）',
          'terminable'  =>  null,
          'stop_at'     =>  null,
          'private_ip'  =>  '172.16.1.8',
          'state'       =>  'running',
        ],[
          'nickname'    => 'dev-web1',
          'instance_id' => 'i-dev-web1',
          'description' => 'テスト#2評価用（24時間運用）',
          'terminable'  => false,
          'stop_at'     => null,
          'private_ip'  => '172.16.0.8',
          'state'       => 'running',
        ]
      ]);
  } //  FakeEc2TableSeeder :: run()
} //  Class FakeEc2TableSeeder
