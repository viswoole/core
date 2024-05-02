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
 * 日期区间验证
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class DateBetween extends RuleAbstract
{
  /**
   * @param string $start
   * @param string $end
   * @param string $message
   */
  public function __construct(
    public string $start,
    public string $end,
    string        $message = '%key必须在 %start - %end 之间'
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
    $fieldDate = strtotime($value);
    $startTime = strtotime($this->start);
    $endTime = strtotime($this->end);
    if ($fieldDate <= $startTime || $fieldDate >= $endTime) {
      throw new ValidateException($this->formatMessage($key, $value));
    }
  }
}
