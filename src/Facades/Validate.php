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
use ViSwoole\Core\Validate\Contract\ValidateInterface;

/**
 * @method static bool check(array $data, bool $batch = false) 数据验证
 * @method static ValidateInterface rules(array $rules) 设置验证规则
 * @method static ValidateInterface message(array $message) 设置自定义提示
 *
 * 优化命令：php viswoole optimize:facade ViSwoole\\Core\\Facades\\Validate
 */
class Validate extends Facade
{

  /**
   * 获取当前Facade对应类名
   *
   * @access protected
   * @return string
   */
  #[Override] protected static function getFacadeClass(): string
  {
    return \ViSwoole\Core\Validate::class;
  }
}
