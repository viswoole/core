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

namespace ViSwoole\Core\Facades;

use Closure;
use Override;
use ViSwoole\Core\App as Container;
use ViSwoole\Core\Facade;

/**
 * App基础容器类
 *
 * @method static void bind(array|string $abstract, mixed $concrete) 注册服务到容器中, 支持批量注册。
 * @method static \ViSwoole\Core\Container|\static single() 获取容器单实例
 * @method static mixed invokeFunction(Closure|string $function, array $vars = []) 执行闭包函数，支持依赖参数注入。
 * @method static mixed make(string $abstract, array $vars = []) 获取容器中的服务，已经存在则直接获取。
 * @method static mixed invoke(callable|string $callable, array $vars = []) 调用反射执行函数、匿名函数、以及类或方法，支持依赖注入。
 * @method static mixed invokeMethod(array|string $method, array $vars = []) 调用反射执行类的方法，支持依赖注入。
 * @method static mixed invokeClass(string $class, array $vars = []) 调用反射执行类的实例化，支持依赖注入。
 * @method static void resolving(Closure|string $abstract, ?Closure $callback = null) 注册一个解析事件回调
 * @method static void removeCallback(Closure|string $abstract, ?Closure $callback = null) 删除解析事件回调
 * @method static object get(string $id) 获取容器中的对象实例
 * @method static bool hasBind(string $abstract) 判断标识或接口是否已经绑定
 * @method static bool has(string $id) 通过标识或接口类名判断是否已经绑定或注册单例
 * @method static bool exists(string $abstract) 判断容器中是否注册单实例
 * @method static void remove(string $abstract) 删除容器中的服务实例
 * @method static int count() 获取容器中实例的数量
 *
 * 优化命令：php viswoole optimize:facade ViSwoole\\Core\\Facades\\App
 */
class App extends Facade
{

  /**
   * 获取当前Facade对应类名
   *
   * @access protected
   * @return string
   */
  #[Override] protected static function getFacadeClass(): string
  {
    return Container::class;
  }
}
