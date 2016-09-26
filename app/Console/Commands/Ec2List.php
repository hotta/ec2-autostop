<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Ec2AutoFactory;

class Ec2List extends Ec2
{
    /**
     * コンソールコマンドのシグニチャー（コマンド書式定義）
     *
     * @var string
     */
    protected $signature = 'ec2:list';

    /**
     * コンソールコマンドの説明
     *
     * @var string
     */
    protected $description = 'EC2 インスタンスの一覧を表示します';

    /**
     * コマンドインスタンスの生成
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    } //  Ec2List :: __construct()

    /**
     * コンソールコマンドの実行
     *
     * @return mixed
     */
    public function handle()
    {
      $ec2 = new Ec2AutoFactory;
      $filtered = $ec2->all();
      print "
Nickname      Private IP    Status             Instance ID
------------------------------------------------------------
";
      foreach ($filtered as $i => $e)  {
        printf("%-14s%-14s%-15s%-20s\n",
          $e['nickname'],
          $e['private_ip'],
          $e['state'],
          $e['instance_id']);
      }
    } //  Ec2List :: handle()

} //  class Ec2List
