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

use PHPUnit\Framework\TestCase;
use ViSwoole\Core\Container;

class A
{

}

class B
{
  public function test(A $a): A
  {
    return $a;
  }
}

class ContainerTest extends TestCase
{
  /**
   * 测试绑定和获取
   *
   * @return void
   */
  public function testBindAndMake()
  {
    // 测试绑定
    Container::single()->bind('func', function () {
      return 'func';
    });
    // 测试make方法并断言输出值
    self::assertEquals('func', Container::single()->make('func'));
  }
}
