<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use AWS;
use App\Ec2Auto;

class Ec2 extends Command
{
  /**
   * コマンドインスタンスの生成
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();
  } //  Ec2 :: __construct()

  /**
   * 制御対象インスタンスの取得
   *
   * @return Ec2Auto array
   */
  public function getInstanceInfo(Ec2Auto $ec2)
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
      if (! $entry = $ec2->getInstanceById($instance_id))  {
        dd("インスタンスID $instance_id が見つかりません。");
      }
    } else  {
      if (! $entry = $ec2->getInstanceByName($nickname))  {
        dd("サーバー名 $nickname が見つかりません。");
      }
    }
//  dd($entry);
    return $entry;
  } //  Ec2 :: getInstanceInfo()

} //  class Ec2 extends Command
