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

namespace ViSwoole\Core\Common;

class Arr
{
  /**
   * 判断是否为索引数组
   *
   * @access public
   * @param array $array
   * @return bool
   */
  public static function isIndexArray(array $array): bool
  {
    return array_values($array) === $array;
  }

  /**
   * 判断是否为关联数组
   *
   * @access public
   * @param array $array
   * @return bool
   */
  public static function isAssociativeArray(array $array): bool
  {
    $keys = array_keys($array);
    return array_keys($keys) !== $keys;
  }

  /**
   * 从数组中弹出指定下标的值
   *
   * @access public
   * @param array $array 数组
   * @param string|int $key 下标键
   * @param mixed|null $default 默认值
   * @return mixed
   */
  public static function arrayPopValue(array &$array, string|int $key, mixed $default = null): mixed
  {
    if (array_key_exists($key, $array)) {
      $value = $array[$key];
      unset($array[$key]);
      return $value;
    } else {
      return $default;
    }
  }
}
