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

namespace ViSwoole\Core\Tests;

use Closure;
use Override;
use PHPUnit\Framework\TestCase;
use Throwable;
use ViSwoole\Core\Contract\MiddlewareInterface;
use ViSwoole\Core\Middleware;
use ViSwoole\HttpServer\Request;

class MiddlewareTest extends TestCase
{

  public function testProcess()
  {
    Request::proxySwooleRequest(\Swoole\Http\Request::create());
    $response = Middleware::process(function () {
      return 1;
    }, [M1::class, M2::class]);
    self::assertEquals(1, $response);
  }

  public function testAdd()
  {
    try {
      Middleware::add(T::class);
    } catch (Throwable $e) {
      echo_log($e->getMessage(), 'ERROR');
      self::assertTrue(true);
    }
  }

  public function testInit()
  {
    Middleware::init();
    self::assertTrue(true);
  }
}

class M1 implements MiddlewareInterface
{

  /**
   * @inheritDoc
   */
  #[Override] public function process(
    Closure $handler
  ): mixed
  {
    echo_log('执行了第一个中间件');
    return $handler();
  }
}

class M2 implements MiddlewareInterface
{

  /**
   * @inheritDoc
   */
  #[Override] public function process(
    Closure $handler
  ): mixed
  {
    echo_log('执行了第二个中间件');
    return $handler();
  }
}

class T
{

}
