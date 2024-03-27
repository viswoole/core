<?php /** @noinspection PhpClassHasTooManyDeclaredMembersInspection */
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

use BadMethodCallException;
use Closure;
use DateTime;
use Exception;

/**
 * 验证规则类
 */
class ValidateRule
{
  /**
   * 默认规则提示
   *
   * @var array
   */
  protected const array DEFAULT_TYPE_MSG = [
    'require' => '{:field}不能为空',
    'number' => '{:field}必须是数字',
    'string' => '{:field}必须是字符串',
    'int' => '{:field}必须是整数',
    'float' => '{:field}必须是浮点小数',
    'price' => '{:field}必须是合法的金额',
    'bool' => '{:field}必须是布尔值',
    'intBool' => '{:field}必须是布尔值或0，1',
    'email' => '{:field}不是有效的电子邮件地址',
    'mobile' => '{:field}不是有效的手机号码',
    'array' => '{:field}必须是数组',
    'date' => '{:field}不是有效的日期时间',
    'alpha' => '{:field}必须是字母',
    'alphaNum' => '{:field}必须是字母和数字',
    'alphaDash' => '{:field}必须是字母、数字、短划线或下划线',
    'activeUrl' => '{:field}不是有效的域名或IP',
    'chs' => '{:field}必须是中文',
    'chsAlpha' => '{:field}必须是中文或字母',
    'chsAlphaNum' => '{:field}必须是中文、字母或数字',
    'chsDash' => '{:field}必须是中文、字母、数字、短划线或下划线',
    'url' => '{:field}不是有效的URL',
    'ip' => '{:field}不是有效的IP地址',
    'dateFormat' => '{:field}必须符合日期格式 {:rule}',
    'in' => '{:field}必须在 {:rule} 中',
    'notIn' => '{:field}必须不在 {:rule} 中',
    'between' => '{:field}必须在 {:rule} - {:rule} 之间',
    'notBetween' => '{:field}不在 {:rule} - {:rule} 之间',
    'length' => '{:field}的长度必须为 {:rule}',
    'max' => '{:field}的长度不能超过 {:rule}',
    'min' => '{:field}的长度不能小于 {:rule}',
    'dateAfter' => '{:field}不能早于 {:rule}',
    'dateBefore' => '{:field}不能晚于 {:rule}',
    'dateBetween' => '{:field}不在 {:rule} - {:rule} 之间',
    'egt' => '{:field}必须大于或等于 {:rule}',
    'gt' => '{:field}必须大于 {:rule}',
    'elt' => '{:field}必须小于或等于 {:rule}',
    'lt' => '{:field}必须小于 {:rule}',
    'eq' => '{:field}必须等于 {:rule}'
  ];
  /**
   * @var array 额外的全局规则方法
   */
  protected static array $rules = [];

  /**
   * 获取默认错误提示
   *
   * @param string $filter 规则
   * @return string
   */
  public static function getError(string $filter): string
  {
    return self::DEFAULT_TYPE_MSG[$filter] ?? '{:field}不符合规则';
  }

  /**
   * 验证字段是否存在且不为空
   * @param mixed $value 字段值
   * @return bool
   */
  public static function require(mixed $value): bool
  {
    return isset($value) && $value !== '';
  }

  /**
   * 验证字段是否为合法的金额 两位小数
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function price(mixed $value): bool
  {
    $numberRegex = '/^(?!0\d)(?!-)\d+(\.\d{0,2})?$/';
    $res = preg_match($numberRegex, (string)$value);
    if (false === $res) return false;
    if (is_int($res)) return $res > 0;
    return false;
  }

  /**
   * 验证字段是否为数字
   *
   *
   * @param mixed $value 字段值
   * @return bool
   */
  public static function number(mixed $value): bool
  {
    return ctype_digit((string)$value);
  }

  /**
   * 验证字段是否为字符串
   *
   * @param mixed $value 字段值
   * @return bool
   */
  public static function string(mixed $value): bool
  {
    return is_string($value);
  }

  /**
   * 验证字段是否为整数
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function int(mixed $value): bool
  {
    return is_int($value);
  }

  /**
   * 验证字段是否为浮点数
   * @param mixed $value 字段值
   * @return bool
   */
  public static function float(mixed $value): bool
  {
    return is_float($value);
  }

  /**
   * 验证字段是否为布尔值
   * @param mixed $value 字段值
   * @return bool
   */
  public static function bool(mixed $value): bool
  {
    return is_bool($value);
  }

  /**
   * 验证字段是否为有效的电子邮件地址
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function email(mixed $value): bool
  {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
  }

  /**
   * 验证字段是否为有效的手机号码
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function mobile(mixed $value): bool
  {
    // 你可以根据具体的手机号码验证规则进行实现
    // 这里只是一个示例
    return preg_match('/^1[3-9]\d{9}$/', $value) === 1;
  }

  /**
   * 验证字段是否为数组
   * @param mixed $value 字段值
   * @return bool
   */
  public static function array(mixed $value): bool
  {
    return is_array($value);
  }

  /**
   * 验证字段是否为布尔值或0、1
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function intBool(mixed $value): bool
  {
    return is_bool($value) || in_array($value, [0, 1], true);
  }

  /**
   * 验证字段是否为有效的日期时间
   * @param mixed $value 字段值
   * @return bool
   */
  public static function date(mixed $value): bool
  {
    // 这里使用 PHP 的 DateTime 类进行日期时间验证
    try {
      new DateTime($value);
      return true;
    } catch (Exception) {
      return false;
    }
  }

  /**
   * 验证字段是否为字母
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function alpha(mixed $value): bool
  {
    return preg_match('/^[A-Za-z]+$/', $value) === 1;
  }

  /**
   * 验证字段是否为字母和数字
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function alphaNum(mixed $value): bool
  {
    return preg_match('/^[A-Za-z0-9]+$/', $value) === 1;
  }

  /**
   * 验证字段是否为字母、数字、短划线或下划线
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function alphaDash(mixed $value): bool
  {
    return preg_match('/^[A-Za-z0-9\-_]+$/', $value) === 1;
  }

  /**
   * 验证字段是否为有效的域名或IP
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function activeUrl(mixed $value): bool
  {
    return filter_var($value, FILTER_VALIDATE_URL) !== false
      || filter_var($value, FILTER_VALIDATE_IP) !== false;
  }

  /**
   * 验证字段是否为中文
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function chs(mixed $value): bool
  {
    return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $value) === 1;
  }

  /**
   * 验证字段是否为中文或字母
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function chsAlpha(mixed $value): bool
  {
    return preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u', $value) === 1;
  }

  /**
   * 验证字段是否为中文、字母或数字
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function chsAlphaNum(mixed $value): bool
  {
    return preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', $value) === 1;
  }

  /**
   * 验证字段是否为中文、字母、数字、短划线或下划线
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function chsDash(mixed $value): bool
  {
    return preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-_]+$/u', $value) === 1;
  }

  /**
   * 验证字段是否为有效的URL
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function url(mixed $value): bool
  {
    return filter_var($value, FILTER_VALIDATE_URL) !== false;
  }

  /**
   * 验证字段是否为有效的IP地址
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function ip(mixed $value): bool
  {
    return filter_var($value, FILTER_VALIDATE_IP) !== false;
  }

  /**
   * 验证字段是否符合指定的日期格式
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 日期格式
   * @return bool
   */
  public static function dateFormat(mixed $value, array $filter): bool
  {
    $result = false;
    foreach ($filter as $format) {
      $date = DateTime::createFromFormat($format, $value);
      $res = $date && $date->format($format) === $value;
      if ($res) {
        $result = true;
        break;
      }
    }
    return $result;
  }

  /**
   * 验证字段是否在指定范围内
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $haystack 验证规则
   * @return bool
   */
  public static function in(mixed $value, array $haystack): bool
  {
    return in_array($value, $haystack);
  }

  /**
   * 验证字段是否不在指定范围内
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $haystack 验证规则
   * @return bool
   */
  public static function notIn(mixed $value, array $haystack): bool
  {
    return !in_array($value, $haystack);
  }

  /**
   * 验证字段是否在指定的范围内
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function between(mixed $value, array $filter): bool
  {
    [$min, $max] = $filter;
    return $value >= $min && $value <= $max;
  }

  /**
   * 验证字段是否不在指定的范围内
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function notBetween(mixed $value, array $filter): bool
  {
    [$min, $max] = $filter;
    return $value < $min || $value > $max;
  }

  /**
   * 验证字段长度是否等于指定长度
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function length(mixed $value, array $filter): bool
  {
    $strLen = mb_strlen((string)$value);
    if (count($filter) === 1) {
      return $strLen === (int)$filter[0];
    } else {
      [$min, $max] = $filter;
      return $strLen <= (int)$max && $strLen >= (int)$min;
    }
  }

  /**
   * 验证字段长度是否不超过指定长度
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function max(mixed $value, array $filter): bool
  {
    $maxLength = (int)$filter[0];
    return mb_strlen((string)$value) <= $maxLength;
  }

  /**
   * 验证字段长度是否不小于指定长度
   *
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function min(mixed $value, array $filter): bool
  {
    $minLength = (int)$filter[0];
    return mb_strlen((string)$value) >= $minLength;
  }

  /**
   * 验证字段是否晚于指定日期
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function dateAfter(mixed $value, array $filter): bool
  {
    $date = $filter[0];
    return strtotime($value) > strtotime($date);
  }

  /**
   * 验证字段是否早于指定日期
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function dateBefore(mixed $value, array $filter): bool
  {
    $date = $filter[0];
    return strtotime($value) < strtotime($date);
  }

  /**
   * 验证字段是否在指定的日期区间内
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function dataBetween(mixed $value, array $filter): bool
  {
    $startDate = strtotime($filter[0]);
    $endDate = strtotime($filter[1]);

    $fieldDate = strtotime($value);
    return $fieldDate >= $startDate && $fieldDate <= $endDate;
  }

  /**
   * 验证字段是否满足大于或等于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function egt(mixed $value, array $filter): bool
  {
    $filterValue = $filter[0];
    return $value >= $filterValue;
  }

  /**
   * 验证字段是否满足大于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function gt(mixed $value, array $filter): bool
  {
    $filterValue = $filter[0];
    return $value > $filterValue;
  }

  /**
   * 验证字段是否满足小于或等于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function elt(mixed $value, array $filter): bool
  {
    $filterValue = $filter[0];
    return $value <= $filterValue;
  }

  /**
   * 验证字段是否满足小于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function lt(mixed $value, array $filter): bool
  {
    $filterValue = $filter[0];
    return $value < $filterValue;
  }

  /**
   * 验证字段是否满足等于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $filter 验证规则
   * @return bool
   */
  public static function eq(mixed $value, array $filter): bool
  {
    $filterValue = $filter[0];
    return $value === $filterValue;
  }

  /**
   * 添加自定义的验证方法
   *
   * @param string $ruleName 规则名称
   * @param Closure $handle 处理函数，函数需返回bool值
   * @param string $message 错误提示信息
   * @return void
   */
  public static function addRule(
    string  $ruleName,
    Closure $handle,
    string  $message = '{:field}验证失败'
  ): void
  {
    self::$rules[$ruleName] = $handle;
    self::DEFAULT_TYPE_MSG[$ruleName] = $message;
  }

  /**
   * 调用用户添加的全局验证方法
   *
   * @param string $name
   * @param array $arguments
   * @return mixed
   */
  public static function __callStatic(string $name, array $arguments)
  {
    if (isset(self::$rules[$name])) return self::$rules[$name](...$arguments);
    throw new BadMethodCallException('ValidateRule ' . $name . ' does not exist.');
  }
}
