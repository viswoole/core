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
use ViSwoole\Core\Facades\Server;
use ViSwoole\Core\Router\RouteGroup;
use ViSwoole\Core\Router\RouteItem;

/**
 * 路由定义类
 *
 * @method static RouteItem get(string|array $paths, string|array|Closure $handler) 定义GET方式访问
 * @method static RouteItem post(string|array $rule, string|array|Closure $handler) 定义POST方式访问
 * @method static RouteItem put(string|array $rule, string|array|Closure $handler) 定义PUT方式访问
 * @method static RouteItem delete(string|array $rule, string|array|Closure $handler) 定义DELETE方式访问
 * @method static RouteItem patch(string|array $rule, string|array|Closure $handler) 定义PATCH方式访问
 * @method static RouteItem head(string|array $rule, string|array|Closure $handler) 定义HEAD方式访问
 * @method static RouteItem any(string|array $rule, string|array|Closure $handler) 定义任意方式访问
 * @method static RouteItem add(string|array $rule, string|array|Closure $handler, string|array $method = null) 添加路由
 * @method static RouteGroup group(string|array|Closure $name, ?Closure $closure = null) 定义分组路由
 * @method static void miss(Closure $handler, string|array $method = '*') 定义miss路由
 */
class Route
{
  protected static ?string $currentServer = null;

  /**
   * 静态调用转发
   * @param string $name
   * @param mixed $arguments
   * @return mixed
   */
  public static function __callStatic(string $name, mixed $arguments)
  {
    return call_user_func_array(
      [Router::collector(self::$currentServer), $name],
      $arguments
    );
  }

  /**
   * 服务路由定义
   * @param string $serverName
   * @param Closure $closure
   * @return void
   */
  public static function server(string $serverName, Closure $closure): void
  {
    // 如果是是当前正在运行的服务，则加载路由
    if ($serverName === Server::getName()) {
      static::$currentServer = $serverName;
      $closure();
      static::$currentServer = null;
    }
  }
}
