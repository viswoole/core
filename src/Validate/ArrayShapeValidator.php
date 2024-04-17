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

use ViSwoole\Core\Common\ArrayObject;

/**
 * ArrayShape用于支持复杂的数组形状校验，能够无限嵌套校验多维数组
 */
abstract class ArrayShapeValidator extends ArrayObject
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
    $newData = [];
    foreach ($this->shape as $field => $metadata) {
      $metadata['alias'] = $this->parent . $metadata['alias'];
      $newData[$field] = $this->validateField($field, $data[$field] ?? null, $metadata);
    }
    $this->exchangeArray($newData);
    return $this;
  }
}
