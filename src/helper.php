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

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ViSwoole\Core\Console\Output;
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
if (!function_exists('getVendorPath')) {
  /**
   * 获取依赖仓库路径,结尾不带/
   * @return string
   */
  function getVendorPath(): string
  {
    return App::getVendorPath();
  }
}
if (!function_exists('getConfigPath')) {
  /**
   * 获取配置仓库路径,结尾不带/
   * @return string
   */
  function getConfigPath(): string
  {
    return App::getConfigPath();
  }
}
if (!function_exists('getAppPath')) {
  /**
   * 获取服务或容器
   *
   * @return string
   */
  function getAppPath(): string
  {
    return App::getAppPath();
  }
}
if (!function_exists('app')) {
  /**
   * 获取服务或容器
   *
   * @param string|null $name 标识或接口,不传返回容器实例
   * @return mixed
   * @throws ContainerExceptionInterface 容器异常
   * @throws NotFoundExceptionInterface 未找到服务
   */
  function app(?string $name = null): mixed
  {
    if (empty($name)) return \ViSwoole\Core\App::factory();
    return \ViSwoole\Core\App::factory()->get($name);
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
if (!function_exists('app_debug')) {
  /**
   * 判断是否开启了debug模式
   *
   * @return bool
   */
  function app_debug(): bool
  {
    return \ViSwoole\Core\App::factory()->isDebug();
  }
}
if (!function_exists('config')) {
  /**
   * 获取配置
   *
   * @param string|null $name 配置名（支持二级 .号分割）
   * @param mixed|null $default 默认值
   * @return mixed
   */
  function config(string $name = null, mixed $default = null): mixed
  {
    return Config::get($name, $default);
  }
}
if (!function_exists('dump')) {
  /**
   * 打印变量
   *
   * @access public
   * @param mixed $data 变量内容
   * @param string $title 标题
   * @param string $color 颜色
   * @param int $backtrace 1为输出调用源，0为不输出
   * @return void
   */
  function dump(
    mixed  $data,
    string $title = 'variable output',
    string $color = Output::COLORS['GREEN'],
    int    $backtrace = 1
  ): void
  {
    Output::dump($data, $title, $color, $backtrace === 0 ? 0 : 2);
  }
}
if (!function_exists('echo_log')) {
  /**
   * 输出一个日志
   *
   * @access public
   * @param int|string $message 日志消息
   * @param string $color 颜色
   * @param int $backtrace 1为输出调用源，0为不输出
   * @return void
   */
  function echo_log(
    int|string $message,
    string     $color = Output::COLORS['GREEN'],
    int        $backtrace = 1
  ): void
  {
    Output::echo($message, $color, $backtrace === 0 ? 0 : 2);
  }
}
if (!function_exists('getAllPhpFiles')) {
  /**
   * 获取目录下所有PHP文件(包括子目录)
   * @param string $dir
   * @return array
   */
  function getAllPhpFiles(string $dir): array
  {
    $phpFiles = [];

    // 打开目录
    if ($handle = opendir($dir)) {
      $dir = rtrim($dir, DIRECTORY_SEPARATOR);
      // 逐个检查目录中的条目
      while (false !== ($entry = readdir($handle))) {
        if ($entry != '.' && $entry != '..') {
          $path = $dir . '/' . $entry;

          // 如果是目录，递归调用该函数
          if (is_dir($path)) {
            $phpFiles = array_merge($phpFiles, getAllPhpFiles($path));
          } elseif (pathinfo($path, PATHINFO_EXTENSION) == 'php') {
            // 如果是.php文件，添加到结果数组中
            $phpFiles[] = $path;
          }
        }
      }

      // 关闭目录句柄
      closedir($handle);
    }

    return $phpFiles;
  }
}
