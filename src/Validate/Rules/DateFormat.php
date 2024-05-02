<?php
declare (strict_types=1);

namespace ViSwoole\Core\Validate\Rules;

use Attribute;
use DateTime;
use Override;
use ViSwoole\Core\Exception\ValidateException;
use ViSwoole\Core\Validate\Contract\RuleAbstract;

/**
 * 日期格式校验s
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
readonly class DateFormat extends RuleAbstract
{
  /**
   * @param array $formats 格式数组
   * @param string $message
   */
  public function __construct(public array $formats, string $message = '%key必须传入 %formats 格式的日期'
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
    $result = false;
    foreach ($this->formats as $format) {
      $date = DateTime::createFromFormat($format, $value);
      $valid = $date && $date->format($format) === $value;
      if ($valid) {
        $result = true;
        break;
      }
    }
    if (!$result) throw new ValidateException($this->formatMessage($key, $value));
  }
}
