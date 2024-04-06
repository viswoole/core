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

use ViSwoole\Core\Server\Http\Request;
use ViSwoole\Core\Server\Http\Response;

interface MiddlewareInterface
{
  /**
   * 执行中间件
   *
   * @param Request $request 请求对象
   * @param Response $response 响应对象
   * @param array $params 额外传递给中间件的参数
   * @return Request|Response 如果返回$request对象则继续往下执行，返回$response对象或其他则停止运行
   */
  public function handle(Request $request, Response $response, array $params = []
  ): Response|Request;
}
