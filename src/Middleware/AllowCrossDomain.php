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

namespace ViSwoole\Core\Middleware;

use Override;
use ViSwoole\Core\Contract\MiddlewareInterface;
use ViSwoole\Core\Server\Http\Request;
use ViSwoole\Core\Server\Http\Response;

class AllowCrossDomain implements MiddlewareInterface
{

  /**
   * 执行中间件
   *
   * @param Request $request
   * @param Response $response
   * @param array $params
   * @return Request|Response 如果返回$request对象则继续往下执行，返回$response对象或其他则停止运行
   */
  #[Override] public function handle(Request $request, Response $response, array $params = []
  ): Response|Request
  {
    $response->setHeader([
      'Access-Control-Allow-Origin' => '*',
      'Access-Control-Allow-Headers' => '*',
      'Access-Control-Allow-Methods' => 'GET,POST,PATCH,PUT,DELETE,OPTIONS,DELETE',
    ]);
    return $request;
  }
}
