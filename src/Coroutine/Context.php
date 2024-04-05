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

namespace ViSwoole\Core\Coroutine;

use RuntimeException;
use ViSwoole\Core\Coroutine;

/**
 * 当前协程上下文
 */
class Context
{
  protected static array $nonCoContext = [];

  /**
   * 从协程上下文中获取记录
   *
   * @param string $key
   * @param mixed|null $default
   * @param int|null $id 协程id
   * @return false|mixed|null
   */
  public static function get(string $key, mixed $default = null, ?int $id = null): mixed
  {
    if (Coroutine::isCoroutine()) {
      return Coroutine::getContext($id ?? Coroutine::id())[$key] ?? $default;
    }

    return static::$nonCoContext[$key] ?? $default;
  }

  /**
   * 往协程上下文中新增记录
   *
   * @param string $key
   * @param mixed $value
   * @param int|null $id 协程id
   * @return void
   */
  public static function set(string $key, mixed $value, ?int $id = null): void
  {
    if (Coroutine::isCoroutine()) {
      Coroutine::getContext($id ?? Coroutine::id())[$key] = $value;
    } else {
      static::$nonCoContext[$key] = $value;
    }
  }

  /**
   * 删除上下文
   *
   * @param string $key 上下文记录键
   * @param int|null $id 协程id，默认为当前协程
   * @return void
   */
  public static function remove(string $key, ?int $id = null): void
  {
    if (Coroutine::isCoroutine()) {
      unset(Coroutine::getContext($id ?? Coroutine::id())[$key]);
    } else {
      unset(static::$nonCoContext[$key]);
    }
  }

  /**
   * 判断属性协程上下文中是否存在
   * @param string $key
   * @param int|null $id 协程id
   * @return bool
   */
  public static function has(string $key, ?int $id = null): bool
  {
    if (Coroutine::isCoroutine()) {
      return isset(Coroutine::getContext($id ?? 0)[$key]);
    }

    return isset(static::$nonCoContext[$key]);
  }

  /**
   * 将上下文从指定协程容器拷贝到当前容器
   *
   * @param int $fromCoroutineId 要复制的协程容器id
   * @param array $keys 要复制的记录键
   */
  public static function copy(int $fromCoroutineId, array $keys = []): void
  {
    $from = Coroutine::getContext($fromCoroutineId);

    if ($from === null) throw new RuntimeException('协程上下文未找到，或已经销毁。');

    $current = Coroutine::getContext();

    $map = $keys ? array_intersect_key(
      $from->getArrayCopy(), array_flip($keys)
    ) : $from->getArrayCopy();

    $current->exchangeArray($map);
  }

  /**
   * 获取容器
   *
   * @access public
   * @param int|null $id
   * @return \Swoole\Coroutine\Context|array|null
   */
  public static function getContainer(?int $id = null): \Swoole\Coroutine\Context|array|null
  {
    if (Coroutine::isCoroutine()) {
      return Coroutine::getContext($id);
    } else {
      return static::$nonCoContext;
    }
  }
}
