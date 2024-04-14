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
use ViSwoole\Core\Common\ArrayObject;
use ViSwoole\Core\Exception\ValidateException;

abstract class ArrayShape extends ArrayObject
{
  use ValidateRuleTrait;
  use ValidateMessageTrait;

  /**
   * Example usage:
   * ```
   *  // 这种写法代表构造参数$array中的name参数必须是字符串，且必须在in数组中
   *  ['name' => ['string','in'=>['小明','小红']]
   * ```
   *
   * @var array
   */
  protected array $rules = [];

  public function __construct(array $data)
  {
    $this->rules = $this->parseRules($this->rules);
    parent::__construct($this->validate($data));
  }

  /**
   * 验证
   *
   * @param array $data
   * @return array
   * @throws ValidateException
   */
  private function validate(array $data): array
  {
    if (empty($this->rules)) throw new ValidateException('验证规则不能为空');
    $newData = [];
    foreach ($this->rules as $field => $structure) {
      // 字段别名或描述
      $alias = $structure['alias'];
      // 拿到待验证的规则 [rule=>params] | 闭包函数
      $rules = $structure['rules'];
      /** @var $value mixed 字段值，不存在则为null */
      $value = $data[$field] ?? null;
      // 开始验证
      if ($rules instanceof Closure) {
        $result = $rules($value, $field, $alias);
        if ($result !== true) {
          $message = is_string($result) ? $result : "$alias 验证失败";
          throw new ValidateException($message);
        }
      } else {
        // 如果参数非必填且为空则跳过验证
        if (empty($value) && !array_key_exists('required', $rules)) {
          $newData[$field] = $value;
          continue;
        }
        // 遍历需要校验的规则
        foreach ($rules as $rule => $params) {
          // 判断规则是否为ArrayShape，如果是则使用ArrayShape进行验证
          if (class_exists($rule) && is_subclass_of($rule, ArrayShape::class)) {
            new $rule(is_array($value) ? $value : []);
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
      $newData[$field] = $value;
    }
    return $newData;
  }
}
