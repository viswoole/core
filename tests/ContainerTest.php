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
  public string $test;
}

class B
{
  public function __construct(A $a)
  {
  }

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
    Container::factory()->bind('func', function () {
      return 'func';
    });
    // 测试make方法并断言输出值
    self::assertEquals('func', Container::factory()->make('func'));
  }

  /**
   * 测试反射调用方法
   *
   * @return void
   */
  public function testInvokeMethod()
  {
    $result = Container::factory()->invokeMethod([B::class, 'test']);
    self::assertInstanceOf(A::class, $result);
  }

  /**
   * 测试反射调用函数
   *
   * @return void
   */
  public function testInvokeFunc()
  {
    $result = Container::factory()->invokeFunction(function (A $a, B $b) {
      return 'test';
    }, ['a' => new A()]);
    self::assertEquals('test', $result);
    $result = Container::factory()->invokeFunction(function (A $a, B $b) {
      return 'test';
    });
    self::assertEquals('test', $result);
  }

  /**
   * 测试反射调用类实例化
   *
   * @return void
   */
  public function testInvokeClass()
  {
    $result = Container::factory()->invokeClass(B::class);
    self::assertInstanceOf(B::class, $result);
  }

  /**
   * 测试注册回调和监听
   *
   * @return void
   */
  public function testCallback()
  {
    $callback = function (A $a, Container $container) {
      $a->test = 'test';
    };
    Container::factory()->resolving(A::class, $callback);
    $a = Container::factory()->invokeClass(A::class);
    static::assertEquals('test', $a->test);

    // 测试注册通用回调
    Container::factory()->resolving($callback);
    $a = Container::factory()->invokeClass(A::class);
    static::assertEquals('test', $a->test);

    // 测试删除回调
    Container::factory()->removeCallback(A::class, $callback);
    Container::factory()->removeCallback('*', $callback);
    $a = Container::factory()->invokeClass(A::class);
    static::assertFalse(isset($a->test));
  }
}
