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
use PHPUnit\Framework\TestCase;
use ViSwoole\Core\Facades\App;
use ViSwoole\Core\Facades\Event;

class EventTest extends TestCase
{
  public Closure $callback;
  public bool $callbackStatus = false;

  public function testOff()
  {
    Event::on('test', $this->callback);
    Event::off('test');
    Event::emit('test');
    static::assertFalse($this->callbackStatus);
  }

  public function testOn()
  {
    Event::on('test', $this->callback);
    Event::emit('test');
    static::assertTrue($this->callbackStatus);
  }

  public function testEmit()
  {
    Event::emit('test');
    static::assertTrue(true);
  }

  public function testOffAll()
  {
    Event::on('test', $this->callback);
    Event::offAll();
    Event::emit('test');
    static::assertFalse($this->callbackStatus);
  }

  protected function setUp(): void
  {
    parent::setUp();
    App::single();
    $this->callback = function () {
      $this->callbackStatus = true;
    };
  }
}
