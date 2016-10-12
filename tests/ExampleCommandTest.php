<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Console\Commands\ExampleCommand;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;

class ExampleCommandTest extends TestCase
{
  public function setUp()
  {
    parent::setUp();
    $this->command = new \App\Console\Commands\ExampleCommand;
    $this->command->setLaravel($this->app);
  }

  /** example:start - 引数なし
   * @expectedException RuntimeException
   * @expectedExceptionMessage Either id or tag needed.
   */
  public function testExampleCommandWithoutMandatoryOptionsWillFail()
  {
    $output = $this->execute();
  }

  /** example:start - id 指定あり */
  public function testExampleCommandWithIdWillSuccess()
  {
    $output = $this->execute(['--id' => 'id1']);
    $this->assertEquals('ExampleCommand called with id=id1', 
      trim($output->fetch()));
  }

  /** example:star - tag 指定あり */
  public function _testExampleCommandWithTagWillSuccess()
  {
    $output = $this->execute(['--tag' => 'tag1']);
    $this->assertEquals('ExampleCommand called with tag=tag1', $output);
  }

  protected function execute(array $params = [])
  {
    $output = new BufferedOutput();
    $this->command->run( new ArrayInput($params), $output);
    return $output;
  }

}
