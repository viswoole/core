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

use Closure;
use InvalidArgumentException;
use ViSwoole\Core\Contract\MiddlewareInterface;
use ViSwoole\Core\Facades\Server;

class Middleware
{
  /**
   * 中间件执行队列
   * @var array
   */
  private static array $queue = [];

  private function __construct()
  {
  }

  /**
   * 初始化
   *
   * @return void
   */
  public static function init(): void
  {
    static $init = false;
    if (!$init) {
      $init = true;
      $path = App::factory()->getAppPath() . '/middleware.php';
      if (file_exists($path)) require_once $path;
    }
  }

  /**
   * 运行中间件
   *
   * @param callable|array|string $handler 最终的处理者
   * @param callable[]|string[] $middlewares 额外的中间件
   * @return mixed
   */
  public static function process(callable|array|string $handler, array $middlewares = []): mixed
  {
    $app = App::factory();
    $middlewares = array_map(function ($middleware) {
      return self::checkMiddleware($middleware);
    }, $middlewares);
    $middlewares = array_merge(self::$queue, $middlewares);
    // 创建中间件管道
    $pipeline = array_reduce(
      array_reverse($middlewares),
      function (Closure $carry, $middleware) {
        return function () use ($middleware, $carry) {
          return App::factory()->invoke($middleware, ['handler' => $carry]);
        };
      },
      $handler
    );
    return $app->invoke($pipeline);
  }

  /**
   * 验证中间件是否有效
   *
   * @param string|Closure $handler
   * @return array|Closure
   */
  public static function checkMiddleware(string|Closure $handler): array|Closure
  {
    if (is_string($handler) && class_exists($handler)) {
      $implements = class_implements($handler);
      if ($implements === false || !in_array(MiddlewareInterface::class, $implements)) {
        throw new InvalidArgumentException(
          '$' . "handler参数值 $handler 不是一个有效的中间件类,必须实现" . MiddlewareInterface::class . '接口'
        );
      } else {
        return [$handler, 'process'];
      }
    }
    return $handler;
  }

  /**
   * 注册一个中间件
   *
   * Example usage:
   *
   * ```
   * // 注册一个闭包中间件，必须调用$handler才能往下执行，支持依赖注入
   * Middleware::add(function (RequestInterface $request, ResponseInterface $response, Closure $handler) {
   *   // 中间件逻辑
   *   return $handler();
   * }, 'http');
   * // 注册一个实现了MiddlewareInterface接口的类
   * Middleware::add(UserAuthMiddleware::class, 'http');
   * ```
   * @param Closure|string $handler 中间件
   * @param string|null $server 服务器名称，默认为null，表示应用于所有服务器
   * @return void
   */
  public static function add(
    Closure|string $handler,
    string         $server = null
  ): void
  {
    // 如果设置了服务器名称，并且当前服务器不是指定的服务器，则不注册该中间件
    if ($server && $server !== Server::getName()) return;
    self::$queue[] = self::checkMiddleware($handler);
  }
}
