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
use ViSwoole\Core\Validate\ShapeTool;


class ShapeToolTest extends TestCase
{

  public function testValidate()
  {
    $res = ShapeTool::validate(
      function (
        UserInfo $userInfo,
        App      $app,
      ): void {
      },
      [
        'userInfo' => [
          'name' => 'string',
          'age' => 10,
          'sex' => 0,
          'address' => 'string',
          'phone' => 'string'
        ]
      ]
    );
    self::assertInstanceOf(UserInfo::class, $res['userInfo']);
  }
}

/**
 * 用户信息
 */
class UserInfo
{
  /**
   * @var string 姓名
   */
  public string $name;
  /**
   * @var int 年龄
   */
  public int $age;
  /**
   * @var int 性别
   */
  public int $sex;
  /**
   * @var string 地址
   */
  public string $address;
  /**
   * @var string 电话
   */
  public string $phone;
}
