<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;

class Ec2TestCase extends TestCase
{
  /** @var \App\Console\Commands\Ec2List  */
  protected $command;

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
    putenv('AWS_EC2_STUB=true');
  } //  Ec2TestCase :: setUp()

  /**
   * artisan コマンドラッパー
   *
   * @return string
   */
  protected function execute(array $params = [])
  {
    $output = new BufferedOutput();
    $this->command->run(
      new ArrayInput($params),
      $output
    );
    return $output;
  } //  Ec2TestCase :: execute()

} //  class Ec2TestCase extends TestCase
