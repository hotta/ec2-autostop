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
          'nickname'      =>  'dev-test1',
          'instance_id'   =>  'i-0987183xx9ef17d77',
          'description'   =>  'テスト#test1',
          'terminable'    =>  false,
          'stop_at'       =>  '12:00',
          'private_ip'    =>  '172.16.1.8',
          'state'         =>  'stopped',
        ],[
          'nickname'    => 'dev-web1',
          'instance_id' => 'i-00c3eaeb0xxx8a242',
          'description' => 'テスト#2評価用',
          'terminable'  => 'true',
          'stop_at'     => '13:00',
          'private_ip'  => '172.16.0.8',
          'state'       => 'running',
        ], [
          'nickname'    => 'dev-dummy1',
          'instance_id' => 'i-xxc3eaeb0426xx242',
          'description' => 'ダミー（24h運用）',
          'terminable'  => 'false',
          'stop_at'     => '14:00',
          'private_ip'  => '172.16.0.8',
          'state'       => 'running',
        ], [
          'nickname'    => 'dev-dummy2',
          'instance_id' => 'i-00xxeaeb042XXa242',
          'description' => 'ダミー#2',
          'terminable'  => 'true',
          'stop_at'     => '15:00',
          'private_ip'  => '172.16.0.99',
          'state'       => 'pending',
        ], [
          'nickname'    => 'dev-dummy3',
          'instance_id' => 'i-00c3xxeb042XXa242',
          'description' => 'ダミー#3',
          'terminable'  => 'true',
          'stop_at'     => '16:00',
          'private_ip'  => '172.16.0.99',
          'state'       => 'stopping',
        ], [
          'nickname'    => 'dev-dummy4',
          'instance_id' => 'i-00c3eaxx042XXa242',
          'description' => 'ダミー#4',
          'terminable'  => 'true',
          'stop_at'     => '17:00',
          'private_ip'  => '172.16.0.99',
          'state'       => 'shutting-down',
        ], [
          'nickname'    => 'dev-dummy5',
          'instance_id' => 'i-00c3eaeb042XXa242',
          'description' => 'ダミー#5',
          'terminable'  => 'true',
          'stop_at'     => '18:00',
          'private_ip'  => '172.16.0.99',
          'state'       => 'terminated',
        ]
      ]);
  } //  FakeEc2TableSeeder :: run()
} //  Class FakeEc2TableSeeder
