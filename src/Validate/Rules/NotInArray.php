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
 * 检查值不能存在于数组中
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class NotInArray extends InArray
{
  public function __construct(
    array  $haystack,
    bool   $strict,
    string $message = '%key不能介于 %haystack 之中'
  )
  {
    parent::__construct($haystack, $strict, $message);
  }

  #[Override] public function validate(string $key, mixed &$value): void
  {
    $message = $this->formatMessage($key, $value);
    $result = in_array($value, $this->haystack, $this->strict);
    if ($result) throw new ValidateException($message);
  }
}
