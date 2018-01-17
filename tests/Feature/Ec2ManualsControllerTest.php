<?php
//
// 注意！以下のケースではテストに失敗する：
//  ・queue:listen や queue:work が動いていて、自動的に状態遷移する。
//  ・phpunit.xml:QUEUE_DRIVER=sync のため、ジョブのキューイングが行われない。

namespace Tests\Feature;

use App\FakeEc2;
use Tests\Ec2TestCase;

class Ec2ManualsControllerTest extends Ec2TestCase
{
  /**
   * テスト前処理：各テストの実行前に毎回呼ばれる
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();
  }

  /**
   *  各テストの実行後に毎回呼ばれる
   *
   *  @return void
   */
  public function tearDown()
  {
    parent::tearDown();
  }

  /**
   * GUI 画面起動（表示対象インスタンスなし）
   */
  public function testEc2ManualsNoTerminable()
  {
    $this->get('/')     //  ここにアクセスすると以下を表示
        ->assertDontSee(self::NICKNAME);  //  サーバー名が表示されない
  } //  Ec2ManualsControllerTest :: testEc2ManualsNoTerminable()

  /**
   * GUI 画面起動（正常：表示対象インスタンス１件のみ）
   */
  public function testEc2ManualsNormal()
  {
      //  インスタンスの状態を running にする
    $this->fake->changeState(self::INSTANCE_ID, 'running');
      //  インスタンスの Terminable（停止可能）を true （自動制御可能）にする
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
      //  インスタンスの Stop_at （停止予定時刻）を１時間後にする
    $this->fake->changeStopAt(self::INSTANCE_ID, $this->oneHourAfter);

    $this->get('/')     //  ここにアクセスすると以下を表示
        ->assertSee(self::NICKNAME)       //  サーバー名
        ->assertSee('動作中')             //  稼働状況
        ->assertSee('停止')               //  ボタン表示
        ->assertSee($this->oneHourAfter)  //  本日の停止予定
        ->assertSee('手動モードへ')       //  ボタン表示
        ;
  } //  Ec2ManualsControllerTest :: testEc2ManualsInit()

  /**
   * Tag:Name=null があっても正常に動作する
   */
  public function testEc2ManualsWhenNicknameIsNullOk()
  {
    $this->fake->changeNickname(self::INSTANCE_ID, null);
    $this->fake->changeDescription(self::INSTANCE_ID, 'Test');
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $this->oneHourAfter);
    $this->get('/')
        ->assertStatus(200);
  }
  /**
   * 「停止」ボタンを押すと「停止処理中」に移行する
   *
   */
  public function testEc2ManualsPressStop()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $this->oneHourAfter);

    $url = sprintf('/manual/stop/%s/%s', self::INSTANCE_ID, self::NICKNAME);
    $this->assertEquals("/manual/stop/i-dev1/dev1", $url);
    $data = [ 'id' => "stop_" . self::INSTANCE_ID];
    $this->assertEquals([ 'id' => "stop_i-dev1"], $data);

    $this->followingRedirects()     //  リダイレクトに追随する
        //  cf. https://github.com/laravel/framework/pull/21771
        ->post($url, $data)         //  '停止' をクリックすると
        ->assertSee('停止処理中')   //  これが表示されて
        ->assertDontSee($this->oneHourAfter);  //  これは見えない
  } //  testEc2ManualsPressStop()

  /**
   * 「起動」ボタンを押すと「起動処理中」に移行する
   *
   */
  public function testEc2ManualsPressStart()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'stopped');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $this->oneHourAfter);

    $url = sprintf('/manual/start/%s/%s', self::INSTANCE_ID, self::NICKNAME);
    $data = [ 'id' => "start_" . self::INSTANCE_ID];

    $this->followingRedirects()     //  リダイレクトに追随する
        ->post($url, $data)         //  '起動' をクリックすると
        ->assertSee('起動処理中')   //  これが表示されて
        ->assertDontSee($this->oneHourAfter);  //  これは見えない
  }

  /**
   * 「手動モードへ」ボタンを押すと「手動モード」に切り替わる
   *
   */
  public function testEc2ManualsPressManualMode()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $this->oneHourAfter);

    $url = sprintf('/manual/to_manual/%s/%s', self::INSTANCE_ID,self::NICKNAME);
    $data = [ 'id' => "manual_" . self::INSTANCE_ID];

    $this->followingRedirects()     //  リダイレクトに追随する
        ->post($url, $data)         //  '手動モードへ' をクリックすると
        ->assertSee('手動モード')   //  これが表示されて
        ->assertDontSee($this->oneHourAfter);  //  これは見えない
  }

  /**
   * Tag:stop_at=null の場合、一覧に表示する（自動制御対象）が、
   *  「手動モードへ」ボタンは表示しない（＝最初から手動モード）
   */
  public function testEc2ManualsWhenStopatIsNull()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, null);
    $this->get('/')
        ->assertSee(self::NICKNAME)           //  サーバー名
        ->assertSee('停止')                   //  ボタン表示
        ->assertDontSee($this->oneHourAfter)  //  本日の停止予定
        ->assertDontSee('手動モードへ')       //  ボタン表示
        ;
  }
}

class Dummy {

  /**
   * Tag:terminabl=false の場合、一覧表に表示しない（制御対象外）
   *
   */
  public function testEc2ManualsWhenTerminalsIsFalse()
  {
    $this->fake->changeNickname(self::INSTANCE_ID, self::NICKNAME);
    $this->fake->changeDescription(self::INSTANCE_ID, 'Test');
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, false);
    $this->fake->changeStopAt(self::INSTANCE_ID, '17:00');
    $this->visit('/')               //  ここにアクセスすると
         ->dontSee(self::NICKNAME); //  ニックネームが表示されない
  }

  /**
   * Tag:Terminable=null（未定義）の場合、一覧表に表示しない（制御対象外）
   *
   */
  public function testEc2ManualsWhenTerminalsIsNull()
  {
    $this->fake->changeNickname(self::INSTANCE_ID, self::NICKNAME);
    $this->fake->changeDescription(self::INSTANCE_ID, 'Test');
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, null);
    $this->fake->changeStopAt(self::INSTANCE_ID, '17:00');
    $this->visit('/')               //  ここにアクセスすると
         ->dontSee(self::NICKNAME); //  ニックネームが表示されない
  }

  /**
   * Tag:Terminable=(無効入力) の場合、一覧表に表示しない（制御対象外）
   *
   */
  public function testEc2ManualsWhenTerminalsIsInvalid()
  {
    $this->fake->changeNickname(self::INSTANCE_ID, self::NICKNAME);
    $this->fake->changeDescription(self::INSTANCE_ID, 'Test');
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, 'INVALID');
    $this->fake->changeStopAt(self::INSTANCE_ID, '17:00');
    $this->visit('/')               //  ここにアクセスすると
         ->dontSee(self::NICKNAME); //  ニックネームが表示されない
  }

  /**
   * Tag:stop_at=(無効値) の場合、「手動モードへ」ボタンは表示しない
   *  現在は stop_at がフォーマットに沿っていない場合、内部で 'manual'
   *  に書き換えている。別途 'manual' カラムを増やして対応する？
   */
  public function testEc2ManualsWhenStopatIsInvalid()
  {
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, 'INVALID');
    $this->get('/')
        ->assertSee(self::NICKNAME)           //  サーバー名
        ->assertSee('停止')                   //  ボタン表示
        ->assertDontSee($this->oneHourAfter)  //  本日の停止予定
        ->assertDontSee('手動モードへ')       //  ボタン表示
        ;
  }

} //  class Ec2ManualsControllerTest extends TestCase
