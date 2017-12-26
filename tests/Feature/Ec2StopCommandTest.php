<?php

namespace Tests\Feature;

use App\Console\Commands\Ec2StopCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;
use App\FakeEc2;

class Ec2StopCommandTest extends Ec2CommandTestCase
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
    $test = new Ec2StopCommand;
    $test->setLaravel($this->app);

    // ApplicationにCommandを登録
    $app = new Application();
    $app->add($test);

    // CommandTesterを被せる
    $command = $app->find('ec2:stop');
    $this->command = new CommandTester($command);

    $this->fake = new FakeEc2;
  }

  /**
   * ec2:stop - 引数エラー：引数なし
   *
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage インスタンスIDかタグ名のいずれかを指定してください。
   */
  public function testEc2StopCommandWithoutArgExpectsException()
  {
    $this->execute();
  }

  /**
   * ec2:stop - 状態不一致（インスタンス起動中）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2StopCommandWhenPending()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'pending');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は起動処理中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:stop - 状態不一致（シャットダウン中）
   *
   * @expectedException RuntimeException
   */
  public function testEc2StopCommandWhenShuttingDown()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'shutting-down');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' はシャットダウン中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:stop - 状態不一致（削除済み）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2StopCommandAlreadyTerminated()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'terminated');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は削除済みです';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:stop - 状態不一致（停止処理中）
   *
   * @expectedException RuntimeException
   *
   */
  public function testEc2StopCommandWhenStopping()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'stopping');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は停止処理中です';
    $this->assertEquals($expected, $output);
  }

  /**
   * ec2:stop - 正常系（インスタンス実行中）
   */
  public function testEc2StopCommandWhenRunningWillSuccess()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
    $this->assertEquals('', trim($output));
  }

  /**
   * ec2:stop - 正常系（インスタンス実行 - 冗長表示）
   *
   */
  public function testEc2StopCommandWhenRunningVerbose()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $output = $this->execute(['-i' => self::INSTANCE_ID, '-v' => '' ],
      [ 'verbosity' => OutputInterface::VERBOSITY_VERBOSE ]);
    $expected = sprintf("%s(%s)を停止しました。",
      self::NICKNAME, self::INSTANCE_ID);
    $this->assertEquals($expected, trim($output));
  }

} //  class Ec2StopCommandTest extends TestCase
