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

namespace ViSwoole\Core\Validate\Rules;

use Attribute;
use Override;
use ViSwoole\Core\Exception\ValidateException;
use ViSwoole\Core\Validate\Contract\RuleAbstract;

/**
 * 区间验证,对数值类型数据进行验证
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class Between extends RuleAbstract
{
  /**
   * @param int|float $start
   * @param int|float $end
   * @param string $message 如果为null则输出默认提示消息
   */
  public function __construct(
    public int|float $start,
    public int|float $end,
    string           $message = '%key必须介于 %start - %end 之间'
  )
  {
    parent::__construct($message);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function validate(string $key, mixed &$value): void
  {
    if (!is_numeric($value)) {
      throw new ValidateException($this->formatMessage($key, $value));
    } else {
      $value = floatval($value);
    }
    if ($value < $this->start || $value > $this->end) {
      throw new ValidateException($this->formatMessage($key, $value));
    }
  }
}
