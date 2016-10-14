<?php

use App\Console\Commands\Ec2RebootCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;
use App\FakeEc2;
require_once 'Ec2CommandTestCase.php';

class Ec2RebootCommandTest extends Ec2CommandTestCase
{
  const NICKNAME    = 'dev-dummy1';
  const INSTANCE_ID = 'i-dev-dummy1';

  private $fake;

  /**
   * テスト前処理
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();
    $test = new Ec2RebootCommand;
    $test->setLaravel($this->app);

    // ApplicationにCommandを登録
    $app = new Application();
    $app->add($test);

    // CommandTesterを被せる
    $command = $app->find('ec2:reboot');
    $this->command = new CommandTester($command);

    $this->fake = new FakeEc2;
  }

  /**
   * ec2:reboot - 引数エラー：引数なし
   *
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage インスタンスIDかタグ名のいずれかを指定してください。
   */
  public function testEc2RebootCommandWithoutArgExpectsException()
  {
    $this->execute();
  }

  /**
   * ec2:reboot - 状態不一致（インスタンス起動中）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2RebootCommandWhenPending()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'pending');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は起動処理中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:reboot - 状態不一致（シャットダウン中）
   *
   * @expectedException RuntimeException
   */
  public function testEc2RebootCommandWhenShuttingDown()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'shutting-down');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' はシャットダウン中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:reboot - 状態不一致（削除済み）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2RebootCommandAlreadyTerminated()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'terminated');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は削除済みです';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:reboot - 状態不一致（停止処理中）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2RebootCommandWhenStopping()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'rebootping');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は停止処理中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:reboot - 正常系１（インスタンス実行中）
   */
  public function testEc2RebootCommandWhenRunningWillSuccess()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $this->assertEquals('', trim($output));
  }

  /**
   * ec2:reboot - 正常系２（インスタンス停止中）
   */
  public function testEc2RebootCommandWhenStoppedWillSuccess()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'stopped');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $this->assertEquals('', trim($output));
  }

  /**
   * ec2:reboot - 正常系（インスタンス実行中 - 冗長表示）
   *
   */
  public function testEc2RebootCommandWhenRunningVerbose()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $output = $this->execute(['-i' => self::INSTANCE_ID, '-v' => '' ],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ]);
    $expected = sprintf("%s(%s)を再起動しました。",
      self::NICKNAME, self::INSTANCE_ID);
    $this->assertEquals($expected, trim($output));
  }

} //  class Ec2RebootCommandTest extends TestCase
