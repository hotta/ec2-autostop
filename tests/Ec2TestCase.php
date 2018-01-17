<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\FakeEc2;
use Mockery;      //  mockery/mockery/library/Mockery.php

class Ec2TestCase extends TestCase
{
  const NICKNAME    = 'dev1';
  const INSTANCE_ID = 'i-dev1';

  protected $fake;  //  EC2 エミュレーション用オブジェクト
  protected $time;  //  停止予定時刻
  protected $mock;  //  Mockery オブジェクト

  use DatabaseTransactions;
    //  Ec2TestCase の親クラス test/TestCase.php のさらに親クラス
    //  Illuminate\Foundation\Testing\TestCase.php にある setup() の中の
    //  $this->setUpTraits(); により、$this->beginDatabaseTransaction() 
    //  の呼び出しが行われる。
    //  なお、Ec2TestCase クラス宣言の前に use で場所を指定しているので、
    //  ここでは単純クラス名のみで use できる

  /**
   *  テストケースクラス全体の前処理
   */
  public static function setUpBeforeClass()
  {
  }

  /**
   * テストケースごとの前処理
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();
    putenv('EC2_EMULATION=true');

    $this->fake = new FakeEc2;

    //  いったん全インスタンスを停止の対象外にする。
    $this->fake->update(['terminable' => false]);
    //  その後、各テストでは特定のインスタンスだけを再度 Terminable=true
    //  に変えることで、テスト対象インスタンスだけが表示されるようにする。
    $this->time = date('H:i:00', time() + 3600); //  現在時刻の１時間後
    $this->mock = Mockery::mock();
    $this->mock->allows()->scheduled()->andReturns($this->time);
  } //  Ec2TestCase :: setUp()

  /**
   *  テストケースごとの後処理
   */
  public function tearDown()
  {
    parent::tearDown();
    //  Illuminate\Foundation\Testing\TestCase.php にある tearDown() の中で、
    //  beginDatabaseTransaction() により登録された
    //  beforeApplicationDestroyed() コールバックが呼び出されることにより、
    //  DB のロールバックが行われる。
  }

  /**
   *  テストケースクラス全体の後処理
   */
  public static function tearDownAfterClass()
  {
  }

} //  class Ec2TestCase extends TestCase
