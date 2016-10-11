<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Ec2Factory;
use InvalidArgumentException;

class Ec2 extends Command
{
  /**
   * Ec2Factory クラスのインスタンス
   *
   * @var \App\Ec2Factory
   */
  protected $ec2;

  /**
   * コマンドインスタンスの生成
   *
   * @return Ec2Factory
   */
  public function __construct()
  {
      parent::__construct();
      $this->ec2 = new Ec2Factory;
  } //  Ec2 :: __construct()

  /**
   * 制御対象インスタンスの取得
   *
   * @throws InvalidArgumentException インスタンス指定がない場合
   *
   * @return array
   */
  public function getInstanceInfo()
  {
    $instance_id = $this->option('instanceid');  //  コマンドラインより
    $nickname = $this->option('nickname');
    if (!$instance_id && !$nickname)  {
      throw new InvalidArgumentException
        ('インスタンスIDかタグ名のいずれかを指定してください。');
    }
    if ($instance_id && $nickname)  {
      throw new InvalidArgumentException
        ('インスタンスIDとタグ名は、いずれか１つを指定してください。');
    }
    if ($instance_id) {
      if (! $entry = $this->ec2->findByInstanceId($instance_id))  {
        throw new InvalidArgumentException
          ("インスタンスID $instance_id が見つかりません。");
      }
    } else  {
      if (! $entry = $this->ec2->findByNickname($nickname))  {
        throw new InvalidArgumentException
          ("サーバー名 $nickname が見つかりません。");
      }
    }
//  dd($entry);
    return $entry;
  } //  Ec2 :: getInstanceInfo()

} //  class Ec2 extends Command
