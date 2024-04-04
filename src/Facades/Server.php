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
 * Swoole服务管理
 *
 * @method static array getConfig() 获取服务完整配置
 * @method static bool startServer() 启动服务
 * @method static string getName() 获取当前运行的服务名称
 * @method static bool getStatus() 获取服务运行状态
 * @method static \Swoole\Server getServer() 获取服务实例
 *
 * 优化命令：php viswoole optimize:facade ViSwoole\\Core\\Facades\\Server
 */
class Server extends Facade
{
  /**
   * 获取当前Facade对应类名
   *
   * @access protected
   * @return string
   */
  #[Override] protected static function getFacadeClass(): string
  {
    return \ViSwoole\Core\Server::class;
  }
}
