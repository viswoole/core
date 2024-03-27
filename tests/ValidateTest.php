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
use ViSwoole\Core\Validate;

class ValidateTest extends TestCase
{
  public function testValidate()
  {
    $validate = new Validate();
    $validate
      ->rules([
        'name' => 'require|max:10',
        'email' => 'email',
      ])->message(['name' => '名字验证错误'])
      ->check([
        'name' => null,
        'email' => 'email'
      ]);
  }
}
