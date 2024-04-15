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

trait ValidateMessageTrait
{

  /**
   * 自定义验证失败提示消息，支持占位符 {:field} 和 {:param}
   * @var array
   */
  protected array $message;

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
