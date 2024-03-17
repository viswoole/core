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

namespace ViSwoole\Core\Exception;

use RuntimeException;

/**
 * 运行时异常基类
 */
class BaseRuntimeException extends RuntimeException
{
  /**
   * 获取错误信息
   * @access public
   * @return array
   */
  final public function getErrorInfo(): array
  {
    // TODO 待实现
    return [];
  }
}
