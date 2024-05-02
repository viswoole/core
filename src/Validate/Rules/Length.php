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
 * 属性或方法参数的长度验证，仅对基本类型为 string、array的属性或参数生效。
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class Length extends RuleAbstract
{
  /**
   * @param int $min 最小长度
   * @param int|null $max 最大长度，不传入时长度必须等于$min
   * @param string|null $message 验证失败的提示信息，为空会输出默认提示消息
   */
  public function __construct(
    public int  $min,
    public ?int $max = null,
    string      $message = null
  )
  {
    if (empty($message)) {
      $message = is_null($this->max)
        ? "%key长度必须为$this->min"
        : "%key长度必须在 $this->min 到 $this->max 之间";
    }
    parent::__construct($message);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function validate(string $key, mixed &$value): void
  {
    if (is_string($value)) {
      $len = strlen(trim($value));
    } elseif (is_array($value)) {
      $len = count($value);
    } else {
      throw new ValidateException($this->formatMessage($key, $value));
    }
    if ($len < $this->min || ($this->max !== null && $len > $this->max)) {
      throw new ValidateException($this->formatMessage($key, $value));
    }
  }
}
