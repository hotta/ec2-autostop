<?php
//
// 注意！以下のケースでは意図しない状態遷移が発生してテストに失敗する：
//  ・queue:listen や queue:work が動いている
//  ・デフォルトの phpunit.xml 
//    - QUEUE_DRIVER=sync になっており、ジョブのキューイングが行われない。

use App\Console\Commands\Ec2AutostopCommand;
use Symfony\Component\Console\Application;
use App\FakeEc2;
require_once 'Ec2TestCase.php';

class Ec2ManualsControllerTest extends Ec2TestCase
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

    $this->fake = new FakeEc2;
    //  いったん全インスタンスを停止の対象外にする
    $this->fake->where(['terminable' => true])
               ->update(['terminable' => false]);
  }

  /**
   * GUI 画面起動
   *
   */
  public function testEc2ManualsInit()
  {
    $one_hour_after = date('H:i:00', time() + 3600); //  現在時刻の１時間後
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $one_hour_after);
    $this->visit('/')             //  ここにアクセスすると
         ->see($one_hour_after)   //  これが表示されており、
         ->see(self::NICKNAME)    //  これが表示されることを確認
         ->see('動作中')
         ->see('停止')
         ->see($one_hour_after)
         ->see('手動モードへ');
  }

  /**
   * 「停止」ボタンを押すと「停止処理中」に移行する
   *
   */
  public function testEc2ManualsPressStop()
  {
    $one_hour_after = date('H:i:00', time() + 3600); //  現在時刻の１時間後
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $one_hour_after);
    $this->visit('/')             //  ここにアクセスすると
         ->see($one_hour_after)   //  これが表示されており、
         ->press('停止')          //  これを押したら
         ->seePageIs('/')         //  ここに戻って
         ->see('停止処理中')      //  これが表示されて
         ->dontSee($one_hour_after)
         ->see('手動モード');
  }

  /**
   * 「起動」ボタンを押すと「起動処理中」に移行する
   *
   */
  public function testEc2ManualsPressStart()
  {
    $one_hour_after = date('H:i:00', time() + 3600); //  現在時刻の１時間後
    $this->fake->changeState(self::INSTANCE_ID, 'stopped');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $one_hour_after);
    $this->visit('/')                 //  ここにアクセスすると
         ->dontSee($one_hour_after)   //  これは表示されず、
         ->press('起動')              //  これを押したら
         ->seePageIs('/')             //  ここに戻って
         ->see('起動処理中')          //  これが表示されて
         ->see('手動モード');         //  これが表示される
  }

  /**
   * 「手動モードへ」ボタンを押すと「手動モード」に切り替わる
   *
   */
  public function testEc2ManualsPressToManualMode()
  {
    $one_hour_after = date('H:i:00', time() + 3600); //  現在時刻の１時間後
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $one_hour_after);
    $this->visit('/')                 //  ここにアクセスすると
         ->see($one_hour_after)       //  これが表示され、
         ->press('手動モードへ')      //  これを押したら
         ->seePageIs('/')             //  ここに戻って
         ->dontSee($one_hour_after)   //  この表示が消えて
         ->see('手動モード');         //  これが表示される
  }

} //  class Ec2ManualsControllerTest extends TestCase
