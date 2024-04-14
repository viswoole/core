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

trait ValidateRuleTrait
{
  /**
   * @var array 验证规则
   */
  protected array $rules;

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
   *  _desc:string,
   * } [ 字段名=>[ 闭包函数 或 [ 规则名称=>[...规则参数] ], _desc=>字段别名或描述]]
   */
  private function parseRules(array $rules): array
  {
    $parsedRules = [];
    foreach ($rules as $field => $rule) {
      $fields = $this->parseField($field);
      // 如果没有设置只验证的字段 或 只验证字段中存在当前字段 则解析验证规则
      $rule = $this->parseRule($rule);
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
  private function parseField(string $field): array
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
  private function parseRule(array|string|Closure $rules): array|Closure
  {
    // 如果是字符串转换则分割为数组
    if (is_string($rules)) $rules = explode('|', $rules);
    if (is_array($rules)) {
      $parsedRule = [];
      foreach ($rules as $ruleName => $params) {
        if (is_int($ruleName)) {
          $parsedRule = array_merge($parsedRule, $this->parseStringRule($params));
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
  private function parseStringRule(string $rules): array
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
}
