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
 * 检查值是否存在于数组中
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class InArray extends RuleAbstract
{
  /**
   * @param array $haystack
   * @param bool $strict 严格检测
   * @param string|null $message 错误提示信息
   */
  public function __construct(
    public array $haystack,
    public bool  $strict,
    string       $message = '%key必须介于 %haystack 之中'
  )
  {
    parent::__construct($message);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function validate(string $key, mixed &$value): void
  {
    $result = in_array($value, $this->haystack, $this->strict);
    if (!$result) throw new ValidateException($this->formatMessage($key, $value));
  }
}
