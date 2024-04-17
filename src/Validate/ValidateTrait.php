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

use Closure;
use ViSwoole\Core\Exception\ValidateException;

trait ValidateTrait
{

  /**
   * 自定义验证失败提示消息，支持占位符 {:field} 和 {:param}
   * @var array
   */
  protected array $message;

  /**
   * @param string $field
   * @param mixed $value
   * @param array $metadata
   * @return mixed 验证成功返回值
   * @throws ValidateException
   */
  private function validateField(string $field, mixed $value, array $metadata): mixed
  {
    // 字段别名或描述
    $alias = $metadata['alias'];
    // 拿到待验证的规则 [rule=>params] | 闭包函数
    $rules = $metadata['rules'];
    // 是否具备默认值
    $default = $metadata['default'];
    // 是否为必填参数
    $required = $metadata['required'];
    if (empty($value)) {
      if ($required) {
        throw new ValidateException(
          $this->getErrorMessage($field, $alias, 'required', [], $alias . '不能为空')
        );
      } else {
        $value = $default;
      }
    }
    if ($rules instanceof Closure) {
      $result = $rules($value, $alias);
      if ($result !== true) {
        $message = is_string($result) ? $result : "{$alias}验证失败";
        throw new ValidateException($message);
      }
    } else {
      foreach ($rules as $rule => $params) {
        // 判断规则是否为ArrayShape，如果是则使用ArrayShape进行验证
        if (class_exists($rule) && is_subclass_of($rule, ArrayShapeValidator::class)) {
          $arrayShape = new $rule("$alias.");
          $value = $arrayShape->validate(is_array($value) ? $value : []);
        } else {
          $result = ValidateRule::$rule($value, $params);
          if (!$result) {
            $message = $this->getErrorMessage(
              $field,
              $alias,
              $rule,
              $params,
              ValidateRule::getError($rule)
            );
            throw new ValidateException($message);
          }
        }
      }
    }
    return $value;
  }

  /**
   * 获取错误提示信息
   *
   * @param string $field 字段名
   * @param string $alias 默认提示信息
   * @param string $rule 验证规则
   * @param array $filter 验证规则过滤参数
   * @param string $defaultMsg 默认提示消息
   * @return string
   */
  private function getErrorMessage(
    string $field,
    string $alias,
    string $rule,
    array  $filter,
    string $defaultMsg
  ): string
  {
    $msg = $this->message[$field . '.' . $rule] ?? $this->message[$field] ?? $defaultMsg;
    $msg = str_replace('{:field}', $alias, $msg);
    $ruleCount = substr_count($msg, '{:param}');
    if ($ruleCount === 1) {
      $msg = str_replace('{:param}', implode(', ', $filter), $msg);
    } elseif ($ruleCount > 1) {
      foreach ($filter as $param) {
        $msg = preg_replace('/\{:param}/', $param, $msg, 1);
      }
    }
    return $msg;
  }
}
