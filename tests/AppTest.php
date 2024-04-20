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
use ViSwoole\Core\App;

class AppTest extends TestCase
{
  public function testApp()
  {
    $container = App::factory();
    $container->bind('a', myTestServiceProvider::class);
    self::assertInstanceOf(myTestServiceProvider::class, $container->make('a'));
  }

  public function testMake()
  {
    $container = App::factory();
    $container->bind('test', function (string $data) {
      return $data;
    });
    self::assertEquals(
      'viswoole',
      $container->make('test', ['viswoole'])
    );
  }
}

class myTestServiceProvider
{
  public function __construct()
  {
  }
}
