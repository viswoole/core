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
 * 验证是否在指定日期之前
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class DateBefore extends RuleAbstract
{
  /**
   * @param string $datetime datetime
   * @param string $message
   */
  public function __construct(public string $datetime, string $message = '%key必须在 %datetime 之前'
  )
  {
    parent::__construct($message);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function validate(string $key, mixed &$value): void
  {
    if (!is_string($value)) throw new ValidateException($this->formatMessage($key, $value));
    if (strtotime($value) >= strtotime($this->datetime)) {
      throw new ValidateException($this->formatMessage($key, $value));
    }
  }
}
