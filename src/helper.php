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


use ViSwoole\Core\Facades\App;
use ViSwoole\Core\Facades\Config;
use ViSwoole\Core\Facades\Env;

if (!function_exists('getRootPath')) {
  /**
   * 获取项目根目录,结尾不带/
   * @return string
   */
  function getRootPath(): string
  {
    return App::getRootPath();
  }
}
if (!function_exists('app')) {
  /**
   * 获取服务或容器
   *
   * @param string|null $name 标识或接口,不传返回容器实例
   * @return mixed
   */
  function app(?string $name = null): mixed
  {
    if (empty($name)) return App::single();
    return App::get($name);
  }
}
if (!function_exists('container')) {
  /**
   * 获取容器
   *
   * @return App
   */
  function container(): App
  {
    return app();
  }
}
if (!function_exists('env')) {
  /**
   * 获取环境变量的值
   *
   * @param string|null $key 环境变量名（支持二级 .号分割）
   * @param mixed|null $default 默认值
   * @return mixed
   */
  function env(?string $key, mixed $default = null): mixed
  {
    return Env::get($key, $default);
  }
}
if (!function_exists('config')) {
  /**
   * 获取配置
   *
   * @param string|null $name 配置名（支持二级 .号分割）
   * @param mixed|null $default 默认值
   * @return mixed|object
   */
  function config(string $name = null, mixed $default = null): mixed
  {
    return Config::get($name, $default);
  }
}
