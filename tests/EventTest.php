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
use ViSwoole\Core\Facades\Event;

class EventTest extends TestCase
{
  public function testOff()
  {
    $status = false;
    Event::on('test', function () use (&$status) {
      $status = true;
    });
    Event::off('test');
    Event::emit('test');
    static::assertFalse($status);
  }

  public function testOn()
  {
    $status = false;
    Event::on('test', function () use (&$status) {
      $status = true;
    });
    Event::emit('test');
    static::assertTrue($status);
  }

  public function testEmit()
  {
    Event::emit('test');
    static::assertTrue(true);
  }

  public function testOffAll()
  {
    $status = false;
    Event::on('test', function () use (&$status) {
      $status = true;
    });
    Event::offAll();
    Event::emit('test');
    static::assertFalse($status);
  }
}
