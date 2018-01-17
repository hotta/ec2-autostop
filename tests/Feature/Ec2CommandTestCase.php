<?php

namespace Tests\Feature;

class Ec2CommandTestCase extends Ec2TestCase
{
  /** @var \App\Console\Commands\Ec2*  */
  protected $command;

  /**
   * テスト前処理
   *
   * @return void
   */
  public function setUp()
  {
    parent::setUp();
  } //  Ec2CommandTestCase :: setUp()

  /**
   * artisan コマンドラッパー
   * パラメーターの詳細は CommandTester::execute() を参照
   *
   * @return string
   */
  protected function execute(array $input = [], $options = [])
  {
    $this->command->execute($input, $options);
    return $this->command->getDisplay();
  } //  Ec2CommandTestCase :: execute()

} //  class Ec2CommandTestCase extends TestCase
