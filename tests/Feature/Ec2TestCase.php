<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Ec2TestCase extends TestCase
{
  use DatabaseTransactions;
    //  Ec2TestCase の親クラス test/TestCase.php のさらに親クラス
    //  Illuminate\Foundation\Testing\TestCase.php にある setup() の中の
    //  $this->setUpTraits(); により、$this->beginDatabaseTransaction() 
    //  の呼び出しが行われる。
    //  なお、Ec2TestCase クラス宣言の前に use で場所を指定しているので、
    //  ここでは単純クラス名のみで use できる

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
