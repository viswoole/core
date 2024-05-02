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

namespace ViSwoole\Core\Validate\Contract;

/**
 * 抽象验证规则类
 */
readonly abstract class RuleAbstract implements RuleInterface
{
  /**
   * @var string 错误提示消息
   */
  protected string $message;

  /**
   * @param string $message 错误提示信息，支持格式化占位符$key,$value,$任意构造属性，例如：'%key验证失败'
   */
  public function __construct(string $message = '%key验证失败')
  {
    $this->message = $message;
  }

  /**
   * 格式化错误提示消息
   *
   * @param string $key 验证的键
   * @param mixed $value 验证的值
   * @param string|null $message 如果不传入，则使用构造属性中的message
   * @return string
   */
  protected function formatMessage(string $key, mixed $value, string $message = null): string
  {
    if (empty($message)) $message = $this->message;
    // 通过正则表达式匹配格式化规则中的占位符
    preg_match_all('/%(\w+)/', $message, $matches);
    // 获取匹配到的占位符
    $placeholders = $matches[1];
    if (!empty($placeholders)) {
      foreach ($placeholders as $placeholder) {
        if ($placeholder === 'key') {
          $message = str_replace('%key', $key, $message);
        } elseif ($placeholder === 'value') {
          if (!is_string($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
          }
          $message = str_replace('%value', $value, $message);
        } elseif (isset($this->{$placeholder})) {
          if (!is_string($this->{$placeholder})) {
            $message = str_replace(
              "%$placeholder",
              json_encode($this->{$placeholder}, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
              $message
            );
          } else {
            $message = str_replace(
              "%$placeholder",
              $this->{$placeholder},
              $message
            );
          }
        }
      }
    }
    return $message;
  }
}
