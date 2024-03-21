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

// 该文件定义了一些常用助手函数

use ViSwoole\Core\App;

if (!function_exists('getRootPath')) {
  /**
   * 获取项目根目录,结尾不带/
   * @return string
   */
  function getRootPath(): string
  {
    return defined('BASE_PATH') ? BASE_PATH : dirname(realpath(__DIR__), 3);
  }
}
if (!function_exists('app')) {
  /**
   * @param string|null $name 绑定的实例名称
   * @return mixed
   */
  function app(?string $name = null): mixed
  {
    if (empty($name)) return App::single();
    return App::single()->get($name);
  }
}
