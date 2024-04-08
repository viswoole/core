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
use ViSwoole\Core\App;
use ViSwoole\Core\Server\Http\Response;

/**
 * 异常处理类
 */
class Handle
{
  protected array $ignoreReport = [
    HttpException::class,
    ValidateException::class,
    RouteNotFoundException::class
  ];

  public function __construct(protected Response $response, protected App $app)
  {
  }

  /**
   * 处理异常
   *
   * @param Throwable $e
   * @return bool
   */
  public function render(Throwable $e): bool
  {
    $code = 500;
    if ($e instanceof HttpException) {
      $code = $e->getHttpCode();
      $this->response->setHeader($e->getHeaders());
    } else {
      $this->report($e);
    }
    return $this->response->exception(
      $e->getMessage(),
      $e->getCode(),
      $code,
      $this->app->isDebug() ? $e->getTrace() : null
    )->send();
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
      $this->app->log->log($level, $e->getMessage(), $data);
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
