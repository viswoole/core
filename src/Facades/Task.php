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

use Override;
use ViSwoole\Core\Facade;

/**
 * 任务分发
 *
 * @method static \ViSwoole\Core\Server\Task factory() 工厂单例模式
 * @method static void registers(string $prefix, string $topic_class) 批量注册任务主题
 * @method static void register(string $topic, callable $handle) 注册任务
 * @method static void push(string $topic, mixed $data, int $workerIndex = -1, ?callable $callback = null) 推送一个任务
 * @method static bool has(string $topic) 判断任务主题是否存在
 *
 * 优化命令：php viswoole optimize:facade ViSwoole\\Core\\Facades\\Task
 */
class Task extends Facade
{

  /**
   * @inheritDoc
   */
  #[Override] protected static function getFacadeClass(): string
  {
    return \ViSwoole\Core\Server\Task::class;
  }
}
