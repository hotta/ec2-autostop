<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ArtisanTest extends TestCase
{
    /**
     * テスト前処理
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->artisan('migrate:refresh');  //  テーブル作り直し
        $this->artisan('db:seed');          //  テストデータ投入
    }
    /**
     * ec2:start - 引数指定なし
     *
     * @return void
     */
    public function testEc2StartWithoutAnyArguments()
    {
        $this->expectOutputRegex(
          '/インスタンスIDかタグ名のいずれかを指定してください。/');
        $list = $this->artisan('ec2:start');
    }
    /**
     * ec2:list - スタブ入力を使う
     *
     * @return void
     */
    public function testEc2ListExpectOutputText()
    {
        $this->expectOutputRegex('/shutting-down/');
        $list = $this->artisan('ec2:list');
    }
} //  class ArtisanTest extends TestCase
