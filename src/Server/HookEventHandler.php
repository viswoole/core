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

namespace ViSwoole\Core\Server;

use Closure;
use Swoole\Constant;
use Swoole\Server as SwooleServer;

/**
 * Hook Swoole Servers Event
 *
 * @access public
 */
class HookEventHandler
{
  /**
   * 事件映射到本来的方法
   */
  public const array EVENT_MAP = [
    Constant::EVENT_START => 'onStart',
    Constant::EVENT_SHUTDOWN => 'onShutdown',
    Constant::EVENT_FINISH => 'onFinish'
  ];
  /**
   * @var array
   */
  protected static array $hook = [];

  /**
   * HOOK监听服务启动
   *
   * @access public
   * @param SwooleServer $server
   * @return void
   */
  public static function onStart(SwooleServer $server): void
  {
    self::runHook(__FUNCTION__, func_get_args());
  }

  /**
   * 运行用户自定义的监听
   *
   * @param string $event
   * @param array $args
   * @return void
   */
  private static function runHook(string $event, array $args): void
  {
    $handler = self::$hook[$event] ?? null;
    if (!is_null($handler)) {
      if ($handler instanceof Closure) {
        $handler(...$args);
      } else {
        call_user_func_array($handler, $args);
      }
    }
  }

  /**
   * 服务正常关闭事件hook
   *
   * @access public
   * @param SwooleServer $server
   * @return void
   */
  public static function onShutdown(SwooleServer $server): void
  {
    self::runHook(__FUNCTION__, func_get_args());
  }

  /**
   * 任务回调
   *
   * @access public
   * @return void
   */
  public static function onFinish(): void
  {
    self::runHook(__FUNCTION__, func_get_args());
  }

  /**
   * hook
   *
   * @param array $events 监听的事件
   * @return array
   */
  public static function hook(array $events): array
  {
    $events = array_change_key_case($events);
    $sys_events = array_change_key_case(self::EVENT_MAP);
    // $handler 是本类中的方法
    foreach ($sys_events as $eventName => $handler) {
      // 判断hook的事件是否在用户自定义监听中存在
      if (array_key_exists($eventName, $events)) {
        // 如果存在则加入到hook数组中
        self::$hook[$handler] = $events[$eventName];
      }
      // 添加事件到监听配置中
      $events[$eventName] = [self::class, $handler];
    }
    return $events;
  }
}
