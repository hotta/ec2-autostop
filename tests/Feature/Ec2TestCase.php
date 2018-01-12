<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Ec2TestCase extends TestCase
{
  use RefreshDatabase;

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
  }

  /**
   *  テストケースクラス全体の後処理
   */
  public static function tearDownAfterClass()
  {
  }

} //  class Ec2TestCase extends TestCase
