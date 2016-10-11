<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\Ec2AutostopCommand;
use App\Console\Commands\Ec2ListCommand;
use App\Console\Commands\Ec2StartCommand;
use App\Console\Commands\Ec2StopCommand;
use App\Console\Commands\Ec2RebootCommand;

class Ec2ServiceProvider extends ServiceProvider
{
 /**
   * プロバイダーの遅延読み込みをするかどうか
   *
   * @var bool
   */
  protected $defer = true;

  /**
   * アプリケーションサービスの初期化処理
   *
   * @return void
   */
  public function boot()
  {
//    if (config('app.debug'))  {
//      Profiler::attachDebugger();
//    }
  }

  /**
   * アプリケーションサービスの登録
   *
   * @return void
   */
  public function register()
  {
    $this->app->singleton('command.app.ec2.autostop', function () {
      return new Ec2AutostopCommand;
    });
    $this->app->singleton('command.app.ec2.list', function () {
      return new Ec2ListCommand;
    });
    $this->app->singleton('command.app.ec2.start', function () {
      return new Ec2StartCommand;
    });
    $this->app->singleton('command.app.ec2.stop', function () {
      return new Ec2StopCommand;
    });
    $this->app->singleton('command.app.ec2.reboot', function () {
      return new Ec2RebootCommand;
    });
    $this->commands([
      'command.app.ec2.autostop',
      'command.app.ec2.list',
      'command.app.ec2.start',
      'command.app.ec2.stop',
      'command.app.ec2.reboot',
    ]);
  } //  Ec2ServiceProvider :: register()

  public function provides()
  {
    return [
      'command.app.ec2.autostop',
      'command.app.ec2.list',
      'command.app.ec2.start',
      'command.app.ec2.stop',
      'command.app.ec2.reboot',
    ];
  } //  Ec2ServiceProvider :: provides()

} // class Ec2ServiceProvider extends ServiceProvider
