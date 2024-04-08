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

class Handle
{
  protected array $ignoreReport = [
    HttpException::class,
    ValidateException::class,
    RouteNotFoundException::class
  ];

  /**
   * 处理异常
   *
   * @param Throwable $e
   * @return bool
   */
  public function render(Throwable $e): bool
  {
    $code = 500;
    $isHttpException = false;
    if ($e instanceof HttpException) {
      $isHttpException = true;
      $code = $e->getHttpCode();
    } else {
      $this->report($e);
    }
    if ($e instanceof BaseException || $e instanceof BaseRuntimeException) {
      $data = $e->getErrorInfo();
    } else {
      $data = [
        'errCode' => $e->getCode(),
        'errMsg' => $e->getMessage(),
      ];
      if (!$isHttpException && App::factory()->isDebug()) {
        $data['trace'] = $e->getTrace();
      }
    }
    return true;
  }

  public function report(Throwable $e)
  {

  }
}
