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

use Throwable;

/**
 * 路由不存在异常
 */
class RouteNotFoundException extends HttpException
{
  public function __construct(
    string    $message = 'routing resource not found',
    Throwable $previous = null
  )
  {
    parent::__construct(message: $message, httpCode: 404, previous: $previous, code: 404);
  }
}
