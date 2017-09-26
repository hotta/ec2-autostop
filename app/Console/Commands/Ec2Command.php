<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use App\Ec2Factory;
use InvalidArgumentException;

abstract class Ec2Command extends Command
{
  /**
   * Ec2Factory クラスのインスタンス
   *
   * @var \App\Ec2Factory
   */
  protected $ec2;

  /**
   * コンソールコマンドのオプション定義
   *
   * @return mixed
   */

  protected function getOptions()
  {
    return  [
      [
        'instanceid',                   //  名前
        'i',                            //  コマンドのショートカット
        InputOption::VALUE_REQUIRED,    //  モード
        '対象のインスタンスID',         //  説明
        null,                           //  デフォルト値
      ], [
        'nickname',                     //  名前
        null,                           //  コマンドのショートカット
        InputOption::VALUE_REQUIRED,    //  モード
        '対象インスタンスのニックネーム（これらのいずれかを指定）', //  説明
        null,                           //  デフォルト値
      ]
/*
, [
        'verbose',                      //  名前
        'v',                            //  コマンドのショートカット
        InputOption::VALUE_OPTIONAL,    //  モード
        '冗長表示',                     //  説明
        null,                           //  デフォルト値
      ]
*/
    ];
  }

  /**
   * コマンドインスタンスの生成
   *
   * @return Ec2Factory
   */
  public function __construct()
  {
      parent::__construct();
      $this->ec2 = new Ec2Factory;
  } //  Ec2Command :: __construct()

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
  } //  Ec2Command :: getInstanceInfo()

} //  class Ec2Command extends Command
