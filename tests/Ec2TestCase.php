<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\FakeEc2;

class Ec2TestCase extends TestCase
{
  const NICKNAME    = 'dev1';
  const INSTANCE_ID = 'i-dev1';

  protected $fake;  //  EC2 エミュレーション用オブジェクト
  protected $oneHourAfter;  //  停止予定時刻

  use RefreshDatabase;
  //  Ec2TestCase の親クラス test/TestCase.php のさらに親クラス
  //  Illuminate\Foundation\Testing\TestCase.php にある setup() の中の
  //  $this->setUpTraits(); から、$this->refreshDatabase() が呼ばれる。
  //  これにより、各テストケース毎にデータベースがまっさらになる。

  //  なお、Ec2TestCase クラス宣言の前に use で場所を指定しているので、
  //  ここでは単純クラス名のみで use できる

  /**
   *  テストケースクラス全体の前処理
   */
  public static function setUpBeforeClass()
  {
    //  ここは Laravel の初期化前なので、Laravel の機能は使えない。
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

    DB::table('fake_ec2')->insert([
      'nickname'    => self::NICKNAME,
      'instance_id' => self::INSTANCE_ID,
      'description' => 'ダミー#1',
      'terminable'  => false,       //  停止対象外
      'stop_at'     => '14:00',
      'private_ip'  => '172.16.0.8',
      'state'       => 'running',
    ]);
    //  各テストでは必要に応じて Terminable=true にする。
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
