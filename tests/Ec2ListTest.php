<?php

use App\Console\Commands\Ec2ListCommand;
include_once 'Ec2TestCase.php';

class Ec2ListTest extends Ec2TestCase
{
  /**
   * テスト前処理
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();
    $this->command = new Ec2ListCommand();
    $this->command->setLaravel($this->app);
  }

  /**
   * ec2:list - 正常系（引数は存在しない）
   *
   */
  public function testEc2ListCommandWithoutArguments()
  {
    $output = $this->execute();
    $this->assertContains('shutting-down', trim($output->fetch()));
  }

  /**
   * ec2:list - 異常系（余計な引数つき）
   *
   * @expectedException InvalidArgumentException
   */
  public function testEc2ListCommandWithExtraArgs()
  {
    $output = $this->execute(['--dummy']);
  }

} //  class Ec2ListTest extends TestCase
