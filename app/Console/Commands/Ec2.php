<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Ec2AutoFactory;

class Ec2 extends Command
{
  /**
   * Ec2AutoFactory クラスのインスタンス
   *
   * @var \App\Ec2AutoFactory
   */
  protected $ec2;

  /**
   * コマンドインスタンスの生成
   *
   * @return Ec2AutoFactory
   */
  public function __construct()
  {
      parent::__construct();
      $this->ec2 = new Ec2AutoFactory;
  } //  Ec2 :: __construct()

  /**
   * 制御対象インスタンスの取得
   *
   * @return array
   */
  public function getInstanceInfo()
  {
    $instance_id = $this->option('instanceid');  //  コマンドラインより
    $nickname = $this->option('nickname');
    if (!$instance_id && !$nickname)  {
      dd('インスタンスIDかタグ名のいずれかを指定してください。');
    }
    if ($instance_id && $nickname)  {
      dd('インスタンスIDとタグ名は、いずれか１つを指定してください。');
    }
    if ($instance_id) {
      if (! $entry = $this->ec2->findByInstanceId($instance_id))  {
        dd("インスタンスID $instance_id が見つかりません。");
      }
    } else  {
      if (! $entry = $this->ec2->findByNickname($nickname))  {
        dd("サーバー名 $nickname が見つかりません。");
      }
    }
//  dd($entry);
    return $entry;
  } //  Ec2 :: getInstanceInfo()

} //  class Ec2 extends Command
