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
 * 门面抽象类
 */
abstract class Facade
{
  /**
   * 始终创建新的对象实例
   * @var bool
   */
  protected static bool $alwaysNewInstance = false;

  public static function __callStatic($method, $params)
  {
    return call_user_func_array([static::createFacade(), $method], $params);
  }

  /**
   * 创建Facade实例
   * @static
   * @access protected
   * @return object
   */
  protected static function createFacade(): object
  {
    $class = static::getFacadeClass();
    if (static::$alwaysNewInstance) {
      // 每次都创建新的实例
      return Container::single()->invokeClass($class);
    } else {
      return Container::single()->make($class);
    }
  }

  /**
   * 获取当前Facade对应类名
   *
   * @access protected
   * @return string
   */
  abstract protected static function getFacadeClass(): string;
}
