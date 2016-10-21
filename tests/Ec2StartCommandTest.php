<?php

use App\Console\Commands\Ec2StartCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;
use App\FakeEc2;
require_once 'Ec2CommandTestCase.php';

class Ec2StartCommandTest extends Ec2CommandTestCase
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
    $test = new Ec2StartCommand;
    $test->setLaravel($this->app);

    // ApplicationにCommandを登録
    $app = new Application();
    $app->add($test);

    // CommandTesterを被せる
    $command = $app->find('ec2:start');
    $this->command = new CommandTester($command);

    $this->fake = new FakeEc2;
  }

  /**
   * ec2:start - 引数エラー：引数なし
   *
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage インスタンスIDかタグ名のいずれかを指定してください。
   */
  public function testEc2StartCommandWithoutArgExpectsException()
  {
    $this->execute();
  }

  /**
   * ec2:start - 状態不一致（インスタンス起動中）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2StartCommandWhenPending()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'pending');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は起動処理中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:start - 状態不一致（インスタンス実行中）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2StartCommandAlreadyRunning()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' はすでに実行中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:start - 状態不一致（シャットダウン中）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2StartCommandWhenShuttingDown()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'shutting-down');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' はシャットダウン中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:start - 状態不一致（削除済み）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2StartCommandAlreadyTerminated()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'terminated');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は削除済みです';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:start - 状態不一致（停止処理中）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2StartCommandWhenStopping()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'stopping');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は停止処理中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:start - 正常系（インスタンス停止中）
   *
   */
  public function testEc2StartCommandWhenStopped()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'stopped');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $this->assertEquals('', trim($output));
  }

  /**
   * ec2:start - 正常系（インスタンス停止中 - 冗長表示）
   *
   */
  public function testEc2StartCommandWhenStoppedVerbose()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'stopped');
    $output = $this->execute(['-i' => self::INSTANCE_ID, '-v' => null ],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ]);
    $expected = sprintf("%s(%s)を起動しました。",
      self::NICKNAME, self::INSTANCE_ID);
    $this->assertEquals($expected, trim($output));
  }

} //  class Ec2StartCommandTest extends TestCase
