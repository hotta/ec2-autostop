<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Ec2Factory;
use App\Manual;
use App\Http\Requests\ManualRequest;

class ManualsController extends Controller
{
  public function index() {
    $ec2 = new Ec2Factory;
    $servers = $ec2->getTerminables();  //  停止可能インスタンス一覧の取得
//  dd($servers);
    $laravel = app();
    return view('manual.index')
      ->with('version', $laravel::VERSION)
      ->with('timestamp', date('Y-m-d H:i:s'))
      ->with('servers', $servers)
      ->with('ec2_emulation_mode', 
        env('EC2_EMULATION', false) ? 'エミュレーター動作中' : null);
  }

  /**
   * インスタンスの起動
   *
   * @return void
   */
  public function start($instance_id, $nickname)
  {
    $ec2 = new Ec2Factory;
    $ec2->start($instance_id);
    $this->save($instance_id, $nickname);
    return redirect('/')->with('flash_message', 
      "サーバー $nickname を起動しました");
  } //  Ec2Factory :: start()

  /**
   * インスタンスの停止
   *
   * @return void
   */
  public function stop($instance_id, $nickname)
  {
    $ec2 = new Ec2Factory;
    $ec2->stop($instance_id);
    $this->save($instance_id, $nickname);
    return redirect('/')->with('flash_message', 
      "サーバー $nickname を停止しました");
  } //  Ec2Factory :: stop()

  /**
   * 手動モードへ
   *
   * @return void
   */
  public function to_manual($instance_id, $nickname)
  {
    \Log::info(sprintf("%s::%s(%s) called.",
      __CLASS__, __METHOD__, $instance_id));
    $this->save($instance_id, $nickname);
    return redirect('/')->with('flash_message', 
      "サーバー $nickname を手動モードに切り替えました");
  } //  Ec2Factory :: to_manual()

  /**
   * 手動レコード作成
   *
   * @return void
   */
  private function save($instance_id, $nickname)
  {
    $manual = Manual::firstOrCreate([
      't_date'      =>  date('Y-m-d'),
      'instance_id' =>  $instance_id,
      'nickname'    =>  $nickname,
    ]);     //  あれば update 、なければ insert
  } //  Ec2Factory :: save()

} //  class ManualsController extends Controller
