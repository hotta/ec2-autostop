<?php

namespace Tests\Command;

use App\Console\Commands\Ec2AutostopCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;

class Ec2AutostopCommandTest extends Ec2CommandTestCase
{
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
    $this->fake->change(self::INSTANCE_ID, [
      'terminable'  =>  false,
    ]);
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE ]);
    $expect = self::NICKNAME . ' is not terminable. Skipping..';
    $this->assertContains($expect, trim($output));
  }

  /**
   * 実行中以外は読み飛ばすこと
   */
  public function testEc2AutostopWhenNotRunningThenSkip()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'state'       =>  'stopping'
    ]);
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE ]);
    $expect = self::NICKNAME . ' is not runnging. Skipping..';
    $this->assertContains($expect, trim($output));
  }


  /**
   * 実行中以外は読み飛ばすこと（メッセージなし）
   */
  public function testEc2AutostopWhenNotRunningThenSkipWithoutMessage()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'state'       =>  'stopping'
    ]);
    $output = $this->execute();
    $this->assertEmpty(trim($output));
  }

  /**
   * 停止予定時刻前なら読み飛ばすこと
   */
  public function testEc2AutostopBeforeStopAtThenSkip()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'stop_at' =>  date('H:i:00', time() + 60),  //  現在時刻の１分後
    ]);
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE ]);
    $expect = self::NICKNAME . ' stop_at > now. Skipping..';
    $this->assertContains($expect, trim($output));
  }

  /**
   * すべての条件を満たした場合は停止すること
   */
  public function testEc2AutostopMatchAllConditionsThenStop()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'stop_at' =>  date('H:i:00', time() - 60),  //  現在時刻の１分前
    ]);
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ]);
    $expect = self::NICKNAME . ' を停止しました。';
    $this->assertContains($expect, trim($output));
  }

  /**
   * すべての条件を満たした場合は停止すること（メッセージなし）
   */
  public function testEc2AutostopMatchAllConditionsThenStopWithoutMessage()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'stop_at' =>  date('H:i:00', time() - 60),  //  現在時刻の１分前
    ]);
    $output = $this->execute();
    $this->assertEmpty(trim($output));
  }

  /**
   * terminable is invalid 
   */
  public function testEc2AutostopTerminableInvalidThenSkip()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'terminable'  =>  'INVALID',
      'stop_at'     =>  date('H:i:00', time() - 60),  //  現在時刻の１分前
    ]);
    $output = $this->execute();
    $this->assertEmpty(trim($output));

    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE ]);
    $expect = self::NICKNAME . ' is not terminable. Skipping..';
    $this->assertContains($expect, trim($output));
  }

  /**
   * terminable is null
   */
  public function testEc2AutostopTerminableisNullThenSkip()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'terminable'  =>  null,
      'stop_at'     =>  date('H:i:00', time() - 60),  //  現在時刻の１分前
    ]);
    $output = $this->execute();
    $this->assertEmpty(trim($output));

    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE ]);
    $expect = self::NICKNAME . ' is not terminable. Skipping..';
    $this->assertContains($expect, trim($output));
  }

  /**
   * terminable is empty
   */
  public function testEc2AutostopWhenTerminableisEmptyThenSkip()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'terminable'  =>  '',
      'stop_at'     =>  date('H:i:00', time() - 60),  //  現在時刻の１分前
    ]);
    $output = $this->execute();
    $this->assertEmpty(trim($output));

    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE ]);
    $expect = self::NICKNAME . ' is not terminable. Skipping..';
    $this->assertContains($expect, trim($output));
  }

  /**
   * stop_at is invalid
   */
  public function testEc2AutostopStopAtInvalidThenSkip()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'stop_at'     =>  '1:2:3',
    ]);
    $output = $this->execute();
    $this->assertEmpty(trim($output));
//
//  $output = $this->execute([],
//    [ 'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE ]);
//  $expect = self::NICKNAME . ' is not terminable. Skipping..';
//  $this->assertContains($expect, trim($output));
  }

  /**
   * stop_at is null
   */
  public function testEc2AutostopWhenStopAtIsNullThenSkip()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'stop_at'     =>  null,
    ]);
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE ]);
    $expect = self::NICKNAME . ': stop_at="". Skipping..';
    $this->assertContains($expect, trim($output));
  }

  /**
   * stop_at is empty
   */
  public function testEc2AutostopWhenStopAtIsEmptyThenSkip()
  {
    $this->fake->change(self::INSTANCE_ID, [
      'stop_at'     =>  '',
    ]);
    $output = $this->execute([],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE ]);
    $expect = self::NICKNAME . ': stop_at="". Skipping..';
    $this->assertContains($expect, trim($output));
  }

} //  class Ec2AutostopCommandTest extends TestCase
