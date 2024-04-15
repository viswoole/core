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
  protected static array $DEFAULT_MSG = [
    'required' => '{:field}不能为空',
    'number' => '{:field}必须是数字',
    'string' => '{:field}必须是字符串',
    'int' => '{:field}必须是整数',
    'float' => '{:field}必须是浮点小数',
    'price' => '{:field}必须是合法的金额',
    'bool' => '{:field}必须是布尔值',
    'intBool' => '{:field}必须是布尔值或0，1',
    'email' => '{:field}不是有效的电子邮件地址',
    'mobile' => '{:field}不是有效的手机号码',
    'indexArray' => '{:field}必须是索引数组且元素类型必须是{:$param}之一',
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
    'idCard' => '{:field}不是有效的身份证号',
    'ip' => '{:field}不是有效的IP地址',
    'dateFormat' => '{:field}必须符合日期格式 {:$param}',
    'in' => '{:field}必须在 {:$param} 中',
    'notIn' => '{:field}必须不在 {:$param} 中',
    'between' => '{:field}必须在 {:$param} - {:$param} 之间',
    'notBetween' => '{:field}不在 {:$param} - {:$param} 之间',
    'length' => '{:field}的长度必须为 {:$param}',
    'max' => '{:field}的长度不能超过 {:$param}',
    'min' => '{:field}的长度不能小于 {:$param}',
    'dateAfter' => '{:field}不能早于 {:$param}',
    'dateBefore' => '{:field}不能晚于 {:$param}',
    'dateBetween' => '{:field}不在 {:$param} - {:$param} 之间',
    'egt' => '{:field}必须大于或等于 {:$param}',
    'gt' => '{:field}必须大于 {:$param}',
    'elt' => '{:field}必须小于或等于 {:$param}',
    'lt' => '{:field}必须小于 {:$param}',
    'eq' => '{:field}必须等于 {:$param}'
  ];
  /**
   * @var array 额外的全局规则方法
   */
  protected static array $rules = [];

  /**
   * 获取默认错误提示
   *
   * @param string $rule
   * @return string
   */
  public static function getError(string $rule): string
  {
    return self::$DEFAULT_MSG[$rule] ?? '{:field}不符合规则';
  }

  /**
   * 验证身份证号
   *
   * @access public
   * @param mixed $value 字段值
   * @return bool
   */
  public static function idCard(mixed $value): bool
  {
    if (!is_string($value)) return false;
    return preg_match(
        '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/',
        $value
      ) === 1;
  }

  /**
   * 验证字段是否存在且不为空
   * @param mixed $value 字段值
   * @return bool
   */
  public static function required(mixed $value): bool
  {
    return !empty($value);
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
    if (!is_numeric($value)) return false;
    $numberRegex = '/^(?!0\d)(?!-)\d+(\.\d{0,2})?$/';
    $res = preg_match($numberRegex, (string)$value);
    if (false === $res) return false;
    return $res > 0;
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
   * 正则验证
   *
   * @param mixed $value
   * @param array $regex
   * @return bool
   */
  public static function regex(mixed $value, array $regex): bool
  {
    if (!is_string($value) && !is_numeric($value)) return false;
    $result = preg_match($regex[0], (string)$value);
    return $result !== false && $result > 0;
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
    if (!is_numeric($value)) return false;
    return preg_match('/^1[3-9]\d{9}$/', $value) === 1;
  }

  /**
   * 验证字段是否为索引数组
   *
   * @access public
   * @param mixed $array 字段值
   * @param array $validateRules 数组元素需匹配的类型,只支持内置验证规则
   * @return bool
   */
  public static function indexArray(mixed $array, array $validateRules): bool
  {
    // 如果不是索引数组返回false
    if (!array_values($array) === $array) return false;
    if (!empty($validateRules)) {
      // 遍历数组元素判断是否符合规则
      foreach ($array as $item) {
        $valid = false;
        foreach ($validateRules as $rule) {
          if (method_exists(self::class, $rule) && self::$rule($item)) {
            $valid = true;
            break;
          }
        }
        if (!$valid) return false;
      }
    }
    return true;
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
    if (!is_string($value)) return false;
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
    if (!is_string($value)) return false;
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
    if (!is_string($value)) return false;
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
    if (!is_string($value)) return false;
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
    if (!is_string($value)) return false;
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
    if (!is_string($value)) return false;
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
    if (!is_string($value)) return false;
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
   * @param array $formats 日期格式
   * @return bool
   */
  public static function dateFormat(mixed $value, array $formats): bool
  {
    $result = false;
    foreach ($formats as $format) {
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
   * @param array $params 验证规则
   * @return bool
   */
  public static function between(mixed $value, array $params): bool
  {
    [$min, $max] = $params;
    return $value >= $min && $value <= $max;
  }

  /**
   * 验证字段是否不在指定的范围内
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function notBetween(mixed $value, array $params): bool
  {
    [$min, $max] = $params;
    return $value < $min || $value > $max;
  }

  /**
   * 验证字段长度是否等于指定长度
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function length(mixed $value, array $params): bool
  {
    $strLen = mb_strlen((string)$value);
    if (count($params) === 1) {
      return $strLen === (int)$params[0];
    } else {
      [$min, $max] = $params;
      return $strLen <= (int)$max && $strLen >= (int)$min;
    }
  }

  /**
   * 验证字段长度是否不超过指定长度
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function max(mixed $value, array $params): bool
  {
    $maxLength = (int)$params[0];
    return mb_strlen((string)$value) <= $maxLength;
  }

  /**
   * 验证字段长度是否不小于指定长度
   *
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function min(mixed $value, array $params): bool
  {
    $minLength = (int)$params[0];
    return mb_strlen((string)$value) >= $minLength;
  }

  /**
   * 验证字段是否晚于指定日期
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function dateAfter(mixed $value, array $params): bool
  {
    $date = $params[0];
    return strtotime($value) > strtotime($date);
  }

  /**
   * 验证字段是否早于指定日期
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function dateBefore(mixed $value, array $params): bool
  {
    $date = $params[0];
    return strtotime($value) < strtotime($date);
  }

  /**
   * 验证字段是否在指定的日期区间内
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function dataBetween(mixed $value, array $params): bool
  {
    $startDate = strtotime($params[0]);
    $endDate = strtotime($params[1]);

    $fieldDate = strtotime($value);
    return $fieldDate >= $startDate && $fieldDate <= $endDate;
  }

  /**
   * 验证字段是否满足大于或等于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function egt(mixed $value, array $params): bool
  {
    $paramsValue = $params[0];
    return $value >= $paramsValue;
  }

  /**
   * 验证字段是否满足大于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function gt(mixed $value, array $params): bool
  {
    $paramsValue = $params[0];
    return $value > $paramsValue;
  }

  /**
   * 验证字段是否满足小于或等于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function elt(mixed $value, array $params): bool
  {
    $paramsValue = $params[0];
    return $value <= $paramsValue;
  }

  /**
   * 验证字段是否满足小于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function lt(mixed $value, array $params): bool
  {
    $paramsValue = $params[0];
    return $value < $paramsValue;
  }

  /**
   * 验证字段是否满足等于规则
   *
   * @access public
   * @param mixed $value 字段值
   * @param array $params 验证规则
   * @return bool
   */
  public static function eq(mixed $value, array $params): bool
  {
    $paramsValue = $params[0];
    return $value === $paramsValue;
  }

  /**
   * 添加自定义的验证方法
   *
   * @access public
   * @param string $ruleName 规则名称
   * @param Closure $handle 函数接收两个参数为$value和$params，返回值为bool
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
    self::$DEFAULT_MSG[$ruleName] = $message;
  }

  /**
   * 调用用户添加的全局验证方法
   *
   * @param string $name
   * @param array $params
   * @return mixed
   */
  public static function __callStatic(string $name, array $params)
  {
    if (isset(self::$rules[$name])) return self::$rules[$name](...$params);
    throw new BadMethodCallException('ValidateRule ' . $name . ' does not exist.');
  }

  /**
   * 解析规则
   *
   * @param array $rules
   * @return array{
   *  string:array{
   *    string:array{
   *      string,array,
   *    }|Closure,
   *  },
   *  alias:string,
   * } [ 字段名=>[ 闭包函数 或 [ 规则名称=>[...规则参数] ], alias=>字段别名或描述]]
   */
  public static function parseRules(array $rules): array
  {
    $parsedRules = [];
    foreach ($rules as $field => $rule) {
      $fields = self::parseField($field);
      // 如果没有设置只验证的字段 或 只验证字段中存在当前字段 则解析验证规则
      $rule = self::parseRule($rule);
      foreach ($fields as $item) {
        $parsedRules[$item['field']] = ['rules' => $rule, 'alias' => $item['alias']];
      }
    }
    return $parsedRules;
  }

  /**
   * 解析字段
   *
   * @param string $field 字段
   * @return array{int,array{field:string,alias:string}} [字段名，字段别名或描述]
   */
  private static function parseField(string $field): array
  {
    $field = str_replace(' ', '', $field);
    $fields = [];
    foreach (explode(',', $field) as $item) {
      if (str_contains($item, '|')) {
        [$name, $alias] = explode('|', $item, 2);
      } else {
        $alias = $item;
        $name = $item;
      }
      $fields[] = ['field' => $name, 'alias' => $alias];
    }
    return $fields;
  }

  /**
   * 解析验证规则
   *
   * @param array|string|Closure $rules
   * @return array{string,array}|Closure 数组示例[rule=>[...params]]|Closure
   */
  private static function parseRule(array|string|Closure $rules): array|Closure
  {
    // 如果是字符串转换则分割为数组
    if (is_string($rules)) $rules = explode('|', $rules);
    if (is_array($rules)) {
      $parsedRule = [];
      foreach ($rules as $ruleName => $params) {
        if (is_int($ruleName)) {
          $parsedRule = array_merge($parsedRule, self::parseStringRule($params));
        } else {
          $parsedRule[$ruleName] = $params;
        }
      }
      return $parsedRule;
    } else {
      // 闭包
      return $rules;
    }
  }

  /**
   * 解析字符串定义的规则 例如 'require|max:10,in:1,2'
   *
   * @param string $rules
   * @return array{string,array} 返回[规则名称=>参数列表]
   */
  private static function parseStringRule(string $rules): array
  {
    $rules = explode('|', $rules);
    $parsedRule = [];
    foreach ($rules as $rule) {
      // 判断字符串中是否存在:参数分隔符
      if (str_contains($rule, ':')) {
        [$rule, $params] = explode(':', $rule, 2);
        $parsedRule[trim($rule)] = explode(',', trim($params));
      } else {
        // 默认参数空数组
        $parsedRule[trim($rule)] = [];
      }
    }
    return $parsedRule;
  }

  /**
   * 判断是否为数组（包括索引数组，和关联数组）
   *
   * @param mixed $value
   * @return bool
   */
  public function array(mixed $value): bool
  {
    return is_array($value);
  }
}
