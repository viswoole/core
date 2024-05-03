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

use Throwable;
use ViSwoole\Log\Facade\Log;

/**
 * 异常处理类
 */
class Handle
{
  private array $ignoreReport = [
    ValidateException::class,
    RouteNotFoundException::class
  ];

  public function __construct(array $ignoreReport = [])
  {
    $this->ignoreReport = array_merge($this->ignoreReport, $ignoreReport);
  }

  /**
   * 处理异常
   *
   * @param Throwable $e
   * @return void
   */
  public function render(Throwable $e): void
  {
    $this->report($e);
  }

  /**
   * 写入日志
   *
   * @param Throwable $e
   * @return void
   */
  public function report(Throwable $e): void
  {
    if (!$this->isIgnoreReport($e)) {
      $data = [
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTrace(),
      ];
      if (method_exists($e, 'logLevel')) {
        $level = $e->logLevel();
      } elseif (property_exists($e, 'logLevel')) {
        $level = $e->logLevel;
      }
      if (!isset($level)) $level = 'error';
      // 记录异常到日志
      Log::log($level, $e->getMessage(), $data);
    }
  }

  /**
   * 判断是否被忽视不写入日志
   * @param Throwable $exception
   * @return bool
   */
  protected function isIgnoreReport(Throwable $exception): bool
  {
    foreach ($this->ignoreReport as $class) {
      if ($exception instanceof $class) {
        return true;
      }
    }
    return false;
  }
}
