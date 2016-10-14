<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Ec2CommandTestCase extends TestCase
{
  /** @var \App\Console\Commands\Ec2List  */
  protected $command;

  /**
   * テスト前処理
   *
   * @param Object $commandToTest テスト対象クラスのオブジェクト
   * @param string $signature     コマンドのシグニチャー
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();
    $this->artisan('migrate:refresh');//  テーブル作り直し(database/migrations)
    $this->seed();                    //  テストデータ投入(database/seeds)
    putenv('AWS_EC2_STUB=true');
  } //  Ec2CommandTestCase :: setUp()

  /**
   * artisan コマンドラッパー
   * パラメーターの詳細は CommandTester::execute() を参照
   *
   * @return string
   */
  protected function execute(array $input = [], $options = [])
  {
    $this->command->execute($input, $options);
    return $this->command->getDisplay();
  } //  Ec2CommandTestCase :: execute()

} //  class Ec2CommandTestCase extends TestCase
