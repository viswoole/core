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

use Swoole\Server;
use ViSwoole\Core\Console\Output;

/**
 * 默认事件处理类
 */
class EventHandle
{
  /**
   * 监听服务启动
   *
   * @param Server $server
   * @return void
   */
  public static function onStart(Server $server): void
  {
    $serverName = SERVER_NAME;
    Output::echo("$serverName 服务启动 进程PID:" . $server->master_pid, 'NOTICE', 0);
  }

  /**
   * 监听服务停止
   *
   * @return void
   */
  public static function onShutdown(): void
  {
    $serverName = SERVER_NAME;
    Output::echo("$serverName 服务已经安全关闭", 'NOTICE', 0);
  }
}
