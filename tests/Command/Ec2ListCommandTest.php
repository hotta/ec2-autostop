<?php

namespace Tests\Command;

use App\Console\Commands\Ec2ListCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Illuminate\Support\Facades\DB;

class Ec2ListCommandTest extends Ec2CommandTestCase
{
  /**
   * テストケースごとの前処理
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();
    $test = new Ec2ListCommand;
    $test->setLaravel($this->app);

    // ApplicationにCommandを登録
    $app = new Application();
    $app->add($test);

    // CommandTesterを被せる
    $command = $app->find('ec2:list');
    $this->command = new CommandTester($command);
  }

  /**
   * ec2:list - 登録データなし
   *
   */
  public function testEc2ListWithoutData()
  {
    DB::table('fake_ec2')->delete();   //  全件削除
    $output = $this->execute();
    $this->assertNotContains('running', trim($output));
  }


  /**
   * ec2:list - 正常系（引数なし）
   *
   */
  public function testEc2ListWithoutArguments()
  {
    $output = $this->execute();
    $this->assertContains('running', trim($output));
  }

  /**
   * ec2:list - 異常系（余計な引数つき）
   *
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage The "--unknown" option does not exist.
   */
  public function testEc2ListWithExtraArgs()
  {
    $this->execute(['--unknown' => 'dummy']);
  }

  /**
   * ec2:list - 異常系（Nickname に null がセットされた）
   *
   */
  public function testEc2ListNicknameIsNull()
  {
    $this->fake->changeNickname(self::INSTANCE_ID, null);
    $output = $this->execute();
    $this->assertContains('running', trim($output));
  }

} //  class Ec2ListCommandTest extends TestCase
