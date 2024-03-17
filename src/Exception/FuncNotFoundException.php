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

namespace ViSwoole\Core\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/**
 * 找不到方法异常
 * @access class
 */
class FuncNotFoundException extends BaseRuntimeException implements NotFoundExceptionInterface
{
  protected string $func;

  public function __construct(string $message, string $func = '', Throwable $previous = null)
  {
    $this->message = $message;
    $this->func = $func;
    parent::__construct($message, 0, $previous);
  }

  /**
   * 获取方法名
   * @access public
   * @return string
   */
  public function getFunc(): string
  {
    return $this->func;
  }
}
