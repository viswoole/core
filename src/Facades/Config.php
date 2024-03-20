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
 * 配置门面类
 *
 * @method static bool has(string $name) 检测配置是否存在
 * @method static mixed get(string $name = null, mixed $default = null) 获取配置参数
 * @method static void set(string|array $key, mixed $value = null) 设置配置参数
 */
class Config extends Facade
{
  #[Override] protected static function getFacadeClass(): string
  {
    return \ViSwoole\Core\Config::class;
  }
}
