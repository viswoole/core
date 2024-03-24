<?php
/*
 *  +----------------------------------------------------------------------
 *  | ViSwoole [基于swoole开发的高性能快速开发框架]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2024
 *  +----------------------------------------------------------------------
 *  | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  +----------------------------------------------------------------------
 *  | Author: ZhuChongLin <8210856@qq.com>
 *  +----------------------------------------------------------------------
 */

declare (strict_types=1);

namespace ViSwoole\Core;

use Closure;

/**
 * 系统服务基础类
 */
abstract class ServiceProvider
{
  /**
   * @var array 重写该属性，可批量绑定服务['tag'=>server::class]
   */
  protected array $bindings = [];

  public function __construct(protected App $app)
  {
  }

  /**
   * 该方法是在所有系统服务都绑定完毕过后调用，可以在此方法内注册路由，监听事件等
   *
   * @return void
   */
  public function boot(): void
  {
  }

  /**
   * 该方法会在服务注册时调用，在该方法内通过$this->app->bind('服务名', '服务类名');
   *
   * @return void
   */
  public function register(): void
  {
  }

  /**
   * 加载路由
   *
   * @access protected
   * @param string $path 路由路径
   */
  protected function loadRoutesFrom(string $path): void
  {
    $this->registerRoutes(function () use ($path) {
      include $path;
    });
  }

  /**
   * 注册路由
   *
   * @param Closure $closure
   * @return void
   */
  protected function registerRoutes(Closure $closure): void
  {
    // TODO 注册路由
  }
}
