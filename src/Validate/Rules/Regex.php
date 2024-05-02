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
 * 身份证验证
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class Regex extends RuleAbstract
{
  /**
   * @param string $pattern 正则表达式
   * @param string $message 错误提示消息
   */
  public function __construct(
    public string $pattern,
    string        $message = '%key必须符合 $pattern 正则表达式'
  )
  {
    parent::__construct($message);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function validate(string $key, mixed &$value): void
  {
    if (
      (!is_string($value) && !is_numeric($value))
      || !preg_match($this->pattern, (string)$value)
    ) {
      throw new ValidateException(
        $this->formatMessage($key, $value)
      );
    }
  }
}
