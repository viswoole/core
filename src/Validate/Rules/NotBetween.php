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

/**
 * 验证数值是否不在某个区间
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class NotBetween extends Between
{
  public function __construct(
    float|int $start,
    float|int $end,
    string    $message = '%key不能介于 %start - %end 之间'
  )
  {
    parent::__construct($start, $end, $message);
  }

  #[Override] public function validate(string $key, mixed &$value): void
  {
    $message = $this->formatMessage($key, $value);
    if (!is_numeric($value)) {
      throw new ValidateException($message);
    } else {
      $value = floatval($value);
    }
    if ($value >= $this->start || $value <= $this->end) {
      throw new ValidateException($message);
    }
  }
}
