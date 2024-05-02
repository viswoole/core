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
 * filter_var过滤器验证
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class Filter extends RuleAbstract
{
  /**
   * @param int $filter 过滤器ID
   * @param array|int $options 过滤选项
   * @param string $message 错误提示消息
   */
  public function __construct(
    public int       $filter,
    public array|int $options = 0,
    string           $message = '%key验证失败'
  )
  {
    parent::__construct($message);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function validate(string $key, mixed &$value): void
  {
    $result = filter_var($value, $this->filter, $this->options);
    if (!$result) throw new ValidateException($this->formatMessage($key, $value));
  }
}
