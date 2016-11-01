<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class Ec2TestCase extends TestCase
{
  /**
   * テスト前処理
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();
    $this->artisan('migrate:refresh');//  テーブル作り直し(database/migrations)
    $this->seed();                    //  テストデータ投入(database/seeds)
    putenv('EC2_EMULATION=true');
  } //  Ec2TestCase :: setUp()

} //  class Ec2TestCase extends TestCase
