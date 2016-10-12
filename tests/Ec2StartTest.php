<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Console\Commands\Ec2StartCommand;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
include_once 'Ec2TestCase.php';
use App\FakeEc2;

class Ec2StartTest extends Ec2TestCase
{
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
    $this->command = new Ec2StartCommand();
    $this->command->setLaravel($this->app);
    $this->fake = new FakeEc2;
  }

  /**
   * ec2:start - 引数なし
   *
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage インスタンスIDかタグ名のいずれかを指定してください。
   */
  public function testEc2StartCommandWithoutArgExpectsException()
  {
    $output = $this->execute();
  }

  /**
   * ec2:start - 正常系（インスタンス停止中）
   *
   */
  public function testEc2StartCommandWhenStopped()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'stopped');
    $output = $this->execute(['-i' => self::INSTANCE_ID ]);
//  $this->assertNull($output);
  } //  Ec2StartTest :: testEc2StartWhenStopped()

  /**
   * ec2:start - 状態不一致（インスタンス起動中）
   *
   * @expectedException RuntimeException
   *
   */
  public function __testEc2StartCommandWhenPending()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'pending');
    $output = $this->execute(['--instance_id' => self::INSTANCE_ID ]);
    $expected = 'インスタンス ' . self::INSTANCE_ID . ' は起動処理中です';
    $this->assertEquals($expected, $output);
  } //  Ec2StartTest :: testEc2StartWhenPending()

} //  class Ec2StartTest extends TestCase
