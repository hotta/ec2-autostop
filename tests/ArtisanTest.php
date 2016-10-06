<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ArtisanTest extends TestCase
{
  /**
   * テスト前処理
   *
   * @return void
   */
  public function setUp()
  {
      parent::setUp();
      $this->artisan('migrate:refresh');  //  テーブル作り直し
      $this->seed();                      //  テストデータ投入
  }
  /**
   * ec2:list - これは通る
   *
   * @return void
   */
  public function testEc2ListExpectOutputText()
  {
      $this->expectOutputRegex('/shutting-down/');
//    $this->artisan('ec2:list');
      echo <<<__
+------------+-------------+---------------+---------------------+
| Nickname   | Private IP  | Status        | Instance ID         |
+------------+-------------+---------------+---------------------+
| dev-test1  | 172.16.1.8  | stopped       | i-0987183xx9ef17d77 |
| dev-web1   | 172.16.0.8  | running       | i-00c3eaeb0xxx8a242 |
| dev-dummy1 | 172.16.0.8  | running       | i-xxc3eaeb0426xx242 |
| dev-dummy2 | 172.16.0.99 | pending       | i-00xxeaeb042XXa242 |
| dev-dummy3 | 172.16.0.99 | stopping      | i-00c3xxeb042XXa242 |
| dev-dummy4 | 172.16.0.99 | shutting-down | i-00c3eaxx042XXa242 |
| dev-dummy5 | 172.16.0.99 | terminated    | i-00c3eaeb042XXa242 |
+------------+-------------+---------------+---------------------+
__;
  }
  /**
   * ec2:list - これは通らない
   *
   * @ticket
   *
   * @return void
   */
  public function testEc2ListExpectRegexText()
  {
      $this->expectOutputRegex('/shutting-down/');
      $this->artisan('ec2:list');
  }
  /**
   * ec2:start - 引数指定なし
   *
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage インスタンスIDかタグ名のいずれかを指定してください。
   */
  public function testEc2StartWithoutAnyArguments()
  {
      $this->artisan('ec2:start');
  }
} //  class ArtisanTest extends TestCase
