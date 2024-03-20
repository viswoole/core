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

use PHPUnit\Framework\TestCase;
use ViSwoole\Core\Container;

class ContainerTest extends TestCase
{
  public function testContainer()

  {
    $container = new Container();
    $container->bind('test', function () {
      return 'test';
    });
    self::assertEquals('test', $container->make('test'));
  }
}
