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

use ReflectionClass;
use RuntimeException;

/**
 * 事件管理器
 */
class Event
{
  /**
   * example
   * --------------------------------------------------
   * [
   *  'event'=>[
   *      'limit'=>0,
   *      'emit' =>0,
   *      'handle'=>[
   *          class,
   *          method
   *      ],
   *  ]
   * ]
   * @var array 监听器
   */
  protected array $listens = [];

  private function __construct()
  {
    $this->initListen();
  }

  /**
   * 初始化监听器
   * @return void
   */
  private function initListen(): void
  {
    $listen = config('listens', []);
    foreach ($listen as $event => $handle) {
      if (!empty($handle)) {
        foreach ($handle as $item) {
          $this->on($event, $item);
        }
      }
    }
  }

  /**
   * 注册事件监听
   *
   * @param string $event 事件名称
   * @param callable|string $handle 处理方法或类
   * @param int $limit 监听次数0为不限制
   * @return bool
   */
  public function on(string $event, callable|string $handle, int $limit = 0): bool
  {
    $event = strtolower($event);
    if (is_string($handle)) {
      if (class_exists($handle)) {
        // 创建 ReflectionClass 对象
        $refClass = new ReflectionClass($handle);
        // 获取类的方法
        $methods = $refClass->getMethods();
        foreach ($methods as $method) {
          if ($method->isPublic()) {
            if ($method->isStatic()) {
              $handle = $refClass->getName() . '::' . $method->getName();
            } else {
              $handle = [
                $handle,
                $method->getName()
              ];
            }
            $methodName = strtolower($method->getName());
            $this->listens[$event . '.' . $methodName][] = [
              'limit' => $limit,
              'count' => 0,
              'handle' => $handle
            ];
          }
        }
      } else {
        throw new RuntimeException("{$event}事件监听的处理类{$handle}未定义");
      }
    } else {
      $this->listens[$event][] = [
        'limit' => $limit,
        'count' => 0,
        'handle' => $handle
      ];
    }
    return true;
  }

  /**
   * 容器make实例化
   */
  public static function __make(): static
  {
    return self::factory();
  }

  /**
   * 工厂单例模式
   */
  public static function factory(): static
  {
    static $instance = null;
    if ($instance === null) $instance = new static();
    return $instance;
  }

  /**
   * 触发事件
   *
   * @access public
   * @param string $event 事件名称
   * @param array $arguments 需要额外传递的参数
   * @return void
   */
  public function emit(string $event, array $arguments = []): void
  {
    $event = strtolower($event);
    $listens = $this->listens[$event] ?? [];
    foreach ($listens as $index => $listen) {
      if ($listen['limit'] === 0 || $listen['limit'] < $listen['count']) {
        \ViSwoole\Core\Facades\App::invoke($listen['handle'], $arguments);
        $this->listens[$event][$index]['count'] += 1;
        if ($this->listens[$event][$index]['count'] >= $listen['limit']) {
          $this->off($event, $listen['handle']);
        }
      }
    }
  }

  /**
   * 关闭监听
   *
   * @param string $event 事件名称,不区分大小写
   * @param callable|string|null $handle 处理函数或方法,不传则关闭所有监听
   * @return void
   */
  public function off(string $event, callable|string $handle = null): void
  {
    $event = strtolower($event);
    if (isset($this->listens[$event])) {
      if (is_null($handle)) {
        unset($this->listens[$event]);
      } else {
        $index = array_search($handle, $this->listens[$event]);
        if ($index !== false) unset($this->listens[$event][$index]);
      }
    }
  }

  /**
   * 清除所有事件监听
   *
   * @access public
   * @return void
   */
  public function offAll(): void
  {
    $this->listens = [];
  }
}
