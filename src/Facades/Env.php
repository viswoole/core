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
 * Env管理类
 *
 * @method static void set(array|string $env, mixed $value) 设置环境变量值
 * @method static bool has(string $name) 检测是否存在环境变量
 * @method static mixed get(?string $name, mixed $default) 获取环境变量值
 *
 * 优化命令：php viswoole optimize:facade ViSwoole\\Core\\Facades\\Env
 */
class Env extends Facade
{

  /**
   * 获取当前Facade对应类名
   *
   * @access protected
   * @return string
   */
  #[Override] protected static function getFacadeClass(): string
  {
    return \ViSwoole\Core\Env::class;
  }
}
