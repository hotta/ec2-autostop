<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\FakeEc2;

class Ec2TestCase extends TestCase
{
  const NICKNAME    = 'dev1';
  const INSTANCE_ID = 'i-dev1';

  protected $fake;  //  EC2 エミュレーション用オブジェクト
  protected $oneHourAfter;  //  停止予定時刻

  use DatabaseTransactions;
    //  Ec2TestCase の親クラス test/TestCase.php のさらに親クラス
    //  Illuminate\Foundation\Testing\TestCase.php にある setup() の中の
    //  $this->setUpTraits(); により、$this->beginDatabaseTransaction() 
    //  の呼び出しが行われる。
    //  なお、Ec2TestCase クラス宣言の前に use で場所を指定しているので、
    //  ここでは単純クラス名のみで use できる

  //  現在は、DBの内容が想定外の場合テストに失敗する。
  //  トランザクションで元に戻すより、setUp() で migrate:refresh して、
  //  個々のテストケースで必要なレコードのみ登録するほうがよいのかも？
  
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
    DB::table('fake_ec2')->update(['terminable' => false]);
    //  その後、各テストでは特定のインスタンスだけを再度 Terminable=true
    //  に変えることで、テスト対象インスタンスだけが表示されるようにする。
    $this->oneHourAfter = date('H:i:00', time() + 3600); //  現在時刻の１時間後
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
