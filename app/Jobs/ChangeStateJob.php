<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\FakeEc2;

class ChangeStateJob extends Job implements ShouldQueue
{
  use InteractsWithQueue, SerializesModels;

  protected $instance_id;
  protected $state;

  /**
   * 新しいジョブインスタンスの生成
   *
   * @param string $instance_id
   * @param string $state
   *
   * @return void
   */
  public function __construct($instance_id, $state, $delay = 60)
  {
    $this->instance_id = $instance_id;
    $this->state = $state;
    $this->delay = $delay;
  }

  /**
   * ジョブの実行
   *
   * @param FakeEc2 $fake
   *
   * @return void
   */
  public function handle(FakeEc2 $fake)
  {
    $this->delay($this->delay);
    $fake->changeState($this->instance_id, $this->state);
  }
}
