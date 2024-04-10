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

namespace ViSwoole\Core;

use ViSwoole\Core\Server\Contract\RequestInterface;
use ViSwoole\Core\Server\Http\Facades\Request;
use ViSwoole\Core\Server\Http\Facades\Response;

class MiddlewareManager
{
  /**
   * @var array 中间件执行队列
   */
  protected array $middlewares = [];
  /**
   * @var string 服务名称
   */
  protected string $server;

  public function __construct(private readonly App $app)
  {
    // 获取服务名称
    $this->server = $this->app->server->getName();
    // 加载全局中间件
    $this->loadGlobalMiddleware();
  }

  /**
   * 加载全局中间件
   *
   * @return void
   */
  protected function loadGlobalMiddleware(): void
  {
    $middleware = $this->app->config->get(
      'middleware.' . $this->server, []
    );
    $this->middlewares = $this->parsingMiddleware($middleware);
  }

  /**
   * 解析中间件
   *
   * @param array $middlewares
   * @return array
   */
  protected function parsingMiddleware(array $middlewares): array
  {
    $newMiddlewares = [];
    foreach ($middlewares as $key => $argv) {
      if (is_int($key)) {
        $newMiddlewares[$argv] = [];
      } else {
        $newMiddlewares[$key] = $argv;
      }
    }
    return $newMiddlewares;
  }

  /**
   * 运行中间件，执行一个回调
   *
   * @param array $middlewares 中间件
   * @param callable $callback 最终要运行的方法
   * @return mixed
   */
  public function run(array $middlewares, callable $callback): mixed
  {
    $middlewares = array_merge($this->middlewares, $this->parsingMiddleware($middlewares));
    $request = Request::create();
    $response = Response::create();
    foreach ($middlewares as $class => $params) {
      $params[] = $request;
      $params[] = $response;
      $result = $this->app->invokeMethod([$class, 'handler'], $params);
      // 如果返回的不是request对象则直接输出响应
      if ($result instanceof RequestInterface) {
        $request = $result;
      } else {
        return $result;
      }
    }
    $vars = [
      $request,
      $response
    ];
    return $this->app->invoke($callback, $vars);
  }
}

