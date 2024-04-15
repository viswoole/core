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

/**
 * ArrayShape用于支持数组数据结构校验
 */
abstract class ArrayShapeValidator extends ArrayObject
{
  use ValidateMessageTrait;

  /**
   * @var array 缓存解析好的规则结构,减少解析规则时间
   */
  private static array $cacheShape = [];
  /**
   * Example usage:
   * ```
   *  // 这种写法代表构造参数$array中的name参数必须是字符串，且必须在in数组中
   *  ['name' => ['string','in'=>['小明','小红']]
   * ```
   *
   * @var array
   */
  protected array $shape = [];

  /**
   * @param string $parent 父级字段,用于错误提示
   */
  public function __construct(private readonly string $parent = '')
  {
    if (!empty($this->shape)) {
      $cacheKey = md5(get_called_class());
      if (!isset(self::$cacheShape[$cacheKey])) {
        $this->shape = ValidateRule::parseRules($this->shape);
        self::$cacheShape[$cacheKey] = $this->shape;
      } else {
        $this->shape = self::$cacheShape[$cacheKey];
      }
    }
    parent::__construct();
  }

  /**
   * 获取数组结构
   *
   * @access public
   * @return array{string,array}
   */
  public function getShape(): array
  {
    return $this->shape;
  }

  /**
   * 验证数据
   *
   * @param array $data 待验证的数据
   * @return static 验证成功会将数据存于当前对象中，实现了ArrayObject接口，支持像数组一样的操作
   */
  public function validate(array $data): static
  {
    if (empty($this->shape)) throw new ValidateException('验证规则不能为空');
    $newData = [];
    foreach ($this->shape as $field => $structure) {
      // 字段别名或描述
      $alias = $this->parent . $structure['alias'];
      // 拿到待验证的规则 [rule=>params] | 闭包函数
      $rules = $structure['rules'];
      /** @var $value mixed 字段值，不存在则为null */
      $value = $data[$field] ?? null;
      // 开始验证
      if ($rules instanceof Closure) {
        $result = $rules($value, $alias);
        if ($result !== true) {
          $message = is_string($result) ? $result : "{$alias}验证失败";
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
          if (class_exists($rule) && is_subclass_of($rule, ArrayShapeValidator::class)) {
            $arrayShape = new $rule("$alias.");
            $arrayShape->validate(is_array($value) ? $value : []);
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
    $this->exchangeArray($newData);
    return $this;
  }
}
