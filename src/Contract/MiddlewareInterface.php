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

namespace ViSwoole\Core\Contract;


use Closure;
use ViSwoole\HttpServer\Contract\RequestInterface;

interface MiddlewareInterface
{
  /**
   * 中间件处理方法
   *
   * @param RequestInterface $request 请求对象
   * @param Closure $handler 下一个处理程序
   * @return mixed
   */
  public function process(
    Closure $handler
  ): mixed;
}
