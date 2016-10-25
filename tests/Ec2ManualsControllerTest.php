<?php

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
    $one_hour_after = date('H:i:0', time() + 3600);  //  現在時刻の１時間後
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $one_hour_after);
    $this->visit('/')             //  ここにアクセスして
         ->see(self::NICKNAME)    //  これが表示されることを確認
         ->see('動作中')
         ->see('停止')
         ->see($one_hour_after)
         ->see('手動モードへ');
  }

  /**
   * 「停止」ボタンを押すとインスタンスを停止する
   *
   *  注意！crontab で schedule:run が動いていると、バックグラウンドで
   *  状態遷移が起こってテストに失敗するので、コメントアウトしてから行うこと。
   *
   */
  public function testEc2ManualsPressStop()
  {
    $one_hour_after = date('H:i:0', time() + 3600);  //  現在時刻の１時間後
    $this->fake->changeState(self::INSTANCE_ID, 'running');
    $this->fake->changeTerminable(self::INSTANCE_ID, true);
    $this->fake->changeStopAt(self::INSTANCE_ID, $one_hour_after);
    $buttonText = 'stop_' . self::INSTANCE_ID;
    $post_to = '/manual/stop/' . self::INSTANCE_ID . '/' . self::NICKNAME;
    $this->visit('/')             //  ここにアクセスして
         ->press('停止')          //  これを押したら
         ->seePageIs('/')         //  ここに戻って
         ->see('停止処理中')      //  これが表示されて
         ->dontSee($one_hour_after)
         ->see('手動モード');
  }

} //  class Ec2ManualsControllerTest extends TestCase
