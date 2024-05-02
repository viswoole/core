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

namespace ViSwoole\Core\Validate;

use UnitEnum;
use ViSwoole\Core\Common\Arr;
use ViSwoole\Core\Exception\ValidateException;


/**
 * 原子类型验证
 */
final readonly class TypeTool
{
  // PHP内置原子类型
  public const array TYPES = [
    'bool',
    'null',
    'int',
    'float',
    'string',
    'array',
    'object',
    'true',
    'false',
    'boolean',
    'integer',
    'double',
    'iterable',
    'mixed'
  ];

  /**
   * 验证是否为bool
   *
   * @param mixed $value
   * @return bool
   */
  public static function boolean(mixed &$value): bool
  {
    return self::bool($value);
  }

  /**
   * 判断是否为bool
   *
   * @param mixed $value
   * @return bool
   */
  public static function bool(mixed &$value): bool
  {
    $value = match ($value) {
      1, 'true', '1', 'yes', 'on' => true,
      0, '0', 'false', 'no', 'off' => false,
      default => $value,
    };
    return is_bool($value);
  }

  /**
   * 验证是否为整数
   *
   * @param mixed $value
   * @return bool
   */
  public static function integer(mixed &$value): bool
  {
    return self::int($value);
  }

  /**
   * 验证是否为int
   *
   * @param mixed $value
   * @return bool
   */
  public static function int(mixed &$value): bool
  {
    if (is_numeric($value)) $value = intval($value);
    return is_int($value);
  }

  /**
   * 任意类型
   *
   * @param mixed $value
   * @return bool
   */
  public static function mixed(mixed $value): bool
  {
    return true;
  }

  /**
   * 验证是否为double类型
   *
   * @param mixed $value
   * @return bool
   */
  public static function double(mixed &$value): bool
  {
    return self::float($value);
  }

  /**
   * 验证是否为float
   *
   * @param mixed $value
   * @return bool
   */
  public static function float(mixed &$value): bool
  {
    if (is_numeric($value)) $value = floatval($value);
    return is_float($value);
  }

  /**
   * 判断是否为null
   *
   * @param mixed $value
   * @return bool
   */
  public static function null(mixed $value): bool
  {
    return is_null($value);
  }

  /**
   * 验证是否为可低代对象
   *
   * @param mixed $value
   * @return bool
   */
  public static function iterable(mixed $value): bool
  {
    return is_infinite($value);
  }

  /**
   * 验证是否为true
   *
   * @param mixed $value
   * @return bool
   */
  public static function true(mixed $value): bool
  {
    return $value === true;
  }

  /**
   * 验证是否为false
   *
   * @param mixed $value
   * @return bool
   */
  public static function false(mixed $value): bool
  {
    return $value === false;
  }

  /**
   * 检测是否为字符串
   *
   * @param mixed $value
   * @return bool
   */
  public static function string(mixed $value): bool
  {
    return is_string($value);
  }

  /**
   * 验证数组
   *
   * @param mixed $value
   * @return bool
   */
  public static function array(mixed &$value): bool
  {
    if (is_array($value)) {
      $value = array_values($value);
      return true;
    }
    return false;
  }

  /**
   * 验证是否为对象，如果为关联数组则会转换为StdClass对象
   *
   * @param mixed $value
   * @return bool
   */
  public static function object(mixed &$value): bool
  {
    if (is_object($value)) {
      return true;
    } else {
      $result = Arr::isAssociativeArray($value);
      if ($result) $value = (object)$value;
      return $result;
    }
  }

  /**
   * 交集类型验证
   *
   * @param string $type
   * @param mixed $value
   * @return bool
   */
  public static function intersection(string $type, mixed $value): bool
  {
    if (str_starts_with($type, '(')) {
      $type = substr($type, 1, -1);
    }
    $types = explode('&', $type);
    foreach ($types as $type) {
      if (!$value instanceof $type) {
        return false;
      }
    }
    return true;
  }

  /**
   * 判断是否内置原子类型
   *
   * @param string $type
   * @return bool
   */
  public static function isAtomicType(string $type): bool
  {
    return in_array($type, self::TYPES);
  }

  /**
   * 验证枚举
   *
   * @param string $key
   * @param string $enum 枚举类
   * @param mixed $case $case
   * @return UnitEnum
   */
  public static function enum(string $key, string $enum, mixed $case): UnitEnum
  {
    $cases = call_user_func($enum . '::cases');
    $names = [];
    foreach ($cases as $item) {
      $names[$item->name] = $item;
    }
    if (isset($names[$case])) {
      return $names[$case];
    } else {
      $names = implode(',', array_keys($names));
      throw new ValidateException("{$key}必须介于 $names 之间");
    }
  }
}
