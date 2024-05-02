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
readonly class IdCard extends RuleAbstract
{
  public function __construct(string $message = '%key不是有效的身份证号')
  {
    parent::__construct($message);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function validate(string $key, mixed &$value): void
  {
    if (is_string($value)) {
      $result = preg_match(
          '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/',
          $value
        ) === 1;
      if (!$result) throw new ValidateException($this->formatMessage($key, $value));
    } else {
      throw new ValidateException($this->formatMessage($key, $value));
    }
  }
}
