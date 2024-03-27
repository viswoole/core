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
use ViSwoole\Core\Exception\ValidateException;
use ViSwoole\Core\Validate;

class ValidateTest extends TestCase
{
  /**
   * 测试闭包验证，批量验证
   *
   * @return void
   */
  public function testValidate()
  {
    $validate = new Validate();
    try {
      $validate
        ->rules([
          'name' => function ($value) {
            $res = Validate\ValidateRule::require($value);
            return $res ? true : throw new ValidateException('名字验证错误');
          },
          'email|邮箱' => 'email',
        ])->message(['name' => '名字验证错误'])
        ->check([
          'name' => null,
          'email' => 'email'
        ], false);
    } catch (ValidateException $e) {
      var_dump($e->getError());
      static::assertTrue(true);
    }
  }

  /**
   * 测试规则解析
   *
   * @return void
   */
  public function testValidate2()
  {
    $validate = new Validate();
    try {
      $validate
        ->rules([
          'name|名字,email|邮箱' => ['require', 'max:40', 'length' => [1, 25]],
        ])->message(['name' => '名字验证错误'])
        ->check([
          'name' => null,
        ], false);
    } catch (ValidateException $e) {
      var_dump($e->getError());
      static::assertTrue(true);
    }
  }
}
