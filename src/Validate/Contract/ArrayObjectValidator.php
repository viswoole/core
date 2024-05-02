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

namespace ViSwoole\Core\Validate\Contract;

use ViSwoole\Core\Common\ArrayObject;
use ViSwoole\Core\Exception\ValidateException;
use ViSwoole\Core\Validate\ValidateRules;
use ViSwoole\Core\Validate\ValidateTrait;

/**
 * 用于支持复杂的键值对数组既对象验证
 */
abstract class ArrayObjectValidator extends ArrayObject
{
  use ValidateTrait;

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
  protected array $rules = [];

  /**
   * @param string $parent 父级字段,用于错误提示
   */
  final public function __construct(private readonly string $parent = '')
  {
    if (!empty($this->rules)) {
      $cacheKey = md5(get_called_class());
      if (!isset(self::$cacheShape[$cacheKey])) {
        $this->rules = ValidateRules::parseRules($this->rules);
        self::$cacheShape[$cacheKey] = $this->rules;
      } else {
        $this->rules = self::$cacheShape[$cacheKey];
      }
    }
    parent::__construct();
  }

  /**
   * 获取对象验证规则
   *
   * @access public
   * @return array{string,array}
   */
  public function getRules(): array
  {
    return $this->rules;
  }

  /**
   * 验证数据
   *
   * @param mixed $data 待验证的数据
   * @return static 验证成功会将数据存于当前对象中，实现了ArrayObject接口，支持像数组一样的操作
   */
  public function validate(mixed $data): static
  {
    if (!is_array($data) || array_values($data) === $data) {
      throw new ValidateException(rtrim($this->parent, '.') . '的值必须为一个对象');
    }
    $newData = [];
    foreach ($this->rules as $field => $metadata) {
      $metadata['alias'] = $this->parent . $metadata['alias'];
      $newData[$field] = $this->validateField($field, $data[$field] ?? null, $metadata);
    }
    $this->exchangeArray($newData);
    return $this;
  }
}
