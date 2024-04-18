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
 * 容器依赖注入反射执行函数、类不存在时应当抛出的异常
 */
abstract class NotFoundExceptionAbstract extends ContainerException implements NotFoundExceptionInterface
{
  public function __construct(string $message, Throwable $previous = null)
  {
    $this->message = $message;
    parent::__construct($message, 504, $previous);
  }
}
