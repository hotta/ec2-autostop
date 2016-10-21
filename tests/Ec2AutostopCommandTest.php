<?php

use App\Console\Commands\Ec2AutostopCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;
use App\FakeEc2;
require_once 'Ec2CommandTestCase.php';

class Ec2AutostopCommandTest extends Ec2CommandTestCase
{
  const NICKNAME    = 'dev1';
  const INSTANCE_ID = 'i-dev1';

  private $fake;

  /**
   * テスト前処理
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();
    $test = new Ec2AutostopCommand;
    $test->setLaravel($this->app);

    // ApplicationにCommandを登録
    $app = new Application();
    $app->add($test);

    // CommandTesterを被せる
    $command = $app->find('ec2:autostop');
    $this->command = new CommandTester($command);

    $this->fake = new FakeEc2;
  }

  /**
   * パラメーターエラー（余計な引数つき）
   *
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage The "--unknown" option does not exist.
   */
  public function testEc2AutostopWithExtraArgs()
  {
    $this->execute(['--unknown' => 'dummy']);
  }

  /**
   * 手動停止不可（terminable=false）は読み飛ばすこと
   */
  public function testEc2AutostopWhenTerminableisFalseThenSkip()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, false);
    $this->fake->changeStopAt(self::INSTANCE_ID); //  現在時刻の１分前
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ]);
    $expect = self::NICKNAME . ' is not terminable. Skipping..';
    $this->assertContains($expect, trim($output));
  }

  /**
   * 実行中以外は読み飛ばすこと
   */
  public function testEc2AutostopWhenNotRunningThenSkip()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'stopping');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID); //  現在時刻の１分前
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ]);
    $expect = self::NICKNAME . ' is not runnging. Skipping..';
    $this->assertContains($expect, trim($output));
  }

  /**
   * 停止予定時刻前なら読み飛ばすこと
   */
  public function testEc2AutostopBeforeStopAtThenSkip()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, 
      date('H:i:0', time() + 60));      //  現在時刻の１分後
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ]);
    $expect = self::NICKNAME . ' stop_at > now. Skipping..';
    $this->assertContains($expect, trim($output));
  }

  /**
   * すべての条件を満たした場合は停止すること
   */
  public function testEc2AutostopMatchAllConditionsThenStop()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID); //  現在時刻の１分前
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ]);
    $expect = self::NICKNAME . ' Stopped.';
    $this->assertContains($expect, trim($output));
  }

} //  class Ec2AutostopCommandTest extends TestCase
