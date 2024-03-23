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

/**
 * App基础容器类
 *
 * @property Env $env 环境变量管理实例
 * @property Config $config 配置管理实例
 * @property Console $console 命令行管理实例
 */
class App extends Container
{
  protected array $bindings = [
    'app' => App::class,
    'env' => Env::class,
    'config' => Config::class,
    'console' => Console::class
  ];
}
