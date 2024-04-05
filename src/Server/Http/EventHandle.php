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

namespace ViSwoole\Core\Server\Http;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Throwable;

class EventHandle
{
  public static function onRequest(
    Request  $request,
    Response $response
  )
  {
    if ($request->getMethod() === 'OPTIONS') {
      $response->header = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => '*',
        'Access-Control-Allow-Methods' => '*',
      ];
      return $response->end();
    }
    try {
      $psr7Request = \ViSwoole\Core\Server\Http\Request::create($request);
      $psr7Response = \ViSwoole\Core\Server\Http\Response::create($response);
    } catch (Throwable $e) {

    }
    return true;
  }
}
